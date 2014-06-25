<?php
	// load in mysql server configuration (connection string, user/pw, etc)
	include '../db/connect.php';
	// connect to the database
	$con=mysql_connect("$DB_host", "$DB_user", "$DB_pass")or die("cannot connect");
	mysql_select_db("$DB_dbName") or die("cannot select DB");
	
  // run query	
	$query1 ="
SELECT id, working_name as name, functional_group_id as group_id
FROM nodes";

$query2 = "
SELECT  distinct nodes1.id AS source, nodes2.id AS target, 2 as value
	FROM trophic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
UNION
SELECT distinct nodes1.id AS source, nodes2.id AS target, 2 as value	
FROM facilitation_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
UNION
SELECT distinct nodes1.id AS source, nodes2.id AS target, 2 as value
FROM parasitic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
UNION
SELECT distinct nodes1.id AS source, nodes2.id AS target, 2 as value
FROM competition_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)";

	$result1 = mysql_query($query1);
	$result2 = mysql_query($query2);

	$out = array();


	if(mysql_num_rows($result1)){
		while($row=mysql_fetch_row($result1)){
			$name=$row[1];
			$group=$row[2]; 
			$out['nodes'][] = array(name => $name, group=> $group);
		}
	}
	if(mysql_num_rows($result2)){
		while($row=mysql_fetch_row($result2)){
			$source=$row[0];
			$target=$row[1]; 
			$value=$row[2];
			$out['links'][]  = array(source => $source, target=> $target, value=> $value);
		}
	}

mysql_close();

	
  // send response headers to the browser
  // following headers instruct the browser to treat the data as a csv file called export.csv
  header( 'Content-Type: text/csv' );
  header( 'Content-Disposition: attachment;filename=kelpforest_export.csv' );

  // output header row (if atleast one row exists)
  $row = mysql_fetch_assoc( $result );
  if ( $row )
  {
    echocsv( array_keys( $row ) );
  }
  
  // output data rows (if atleast one row exists)
  while ( $row )
  {
    echocsv( $row );
    $row = mysql_fetch_assoc( $result );
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
?>
