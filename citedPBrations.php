<?php
// load in mysql server configuration (connection string, user/pw, etc)
include 'connect.php';
// connect to the database
//$con=mysql_connect("$DB_host", "$DB_user", "$DB_pass")or die("cannot connect");
//mysql_select_db("$DB_dbName") or die("cannot select DB");

function makecite($year,$authors)
{
  if (count($authors) > 2)
  {
    return "({$authors[0][last_name]}, et. al., $year)";
  }
  else if (count($authors) == 2)
  {
    return "({$authors[0][last_name]} & {$authors[1][last_name]}, $year)";
  }
  else
  {
    return "({$authors[0][last_name]}, $year)";
  }
}
// echocsv function
// echo the input array as csv data maintaining consistency with most CSV implementations
// * uses double-quotes as enclosure when necessary
// * uses double double-quotes to escape double-quotes 
// * uses CRLF as a line separator
function echocsv( $fields )
{
  $separator = '';
  foreach ( $fields as $field )
  {
    if ( preg_match( '/\\r|\\n|,|"/', $field ) )
    {
      $field = '"' . str_replace( '"', '""', $field ) . '"';
    }
    echo $separator . $field;
    $separator = ',';
  }
  echo "\r\n";
}
// run query	
$sql = "
  select `n`.working_name, `s`.name AS `stage_name`, `spbr`.prod_biomass_ratio, `spbr`.cite_id AS `observations`, `c`.year AS `year`
  from nodes `n` inner join stages `s`
  on `n`.id = `s`.node_id
  inner join stage_prod_biomass_ratio `spbr`
  on `s`.id = `spbr`.stage_id
  inner join citations `c` on `spbr`.cite_id = `c`.id
  ";


// send response headers to the browser
// following headers instruct the browser to treat the data as a csv file called export.csv

//header( 'Content-Type: text/csv' );
//header( 'Content-Disposition: attachment;filename=kelpforest_export.csv' );


print "<pre>";
$first = true;
$res = $db->query($sql);
if (DB::isError($res)) {
  print_r($res);
}
else {
  while ($res->fetchInto($row,DB_FETCHMODE_ASSOC))
  {
    $cid = $row['observations'];
    $year = $row['year'];
    unset($row['year']);
    $row['observations'] = '';

    if ($first) {
      echocsv(array_keys($row));
      $first = false;
    }


    $cites = array();
    //--------------------------------------------------
    //   foreach ($obs as $observation)
    //   {
    //-------------------------------------------------- 

    $asql = "select a.last_name
      from authors a, citations c, author_cite ac
      where (a.id = ac.author_id and ac.cite_id = c.id) and
      c.id = ?";
    $au = $db->getAll($asql,array($cid));
    $cites[] = makecite($year,$au);			 

    //--------------------------------------------------
    //   }
    //-------------------------------------------------- 
    $row['observations'] = implode(', ',$cites);

    echocsv($row);

  }


}
print "</pre>";
?>
