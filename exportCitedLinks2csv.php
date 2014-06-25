<?php
	// load in mysql server configuration (connection string, user/pw, etc)
	include '../new/connect.php';
	// connect to the database
	//$con=mysql_connect("$DB_host", "$DB_user", "$DB_pass")or die("cannot connect");
	//mysql_select_db("$DB_dbName") or die("cannot select DB");
	
  // run query	
	$sql = "
SELECT  links.id as interaction_id, nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'trophic' AS type
	FROM trophic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  links.id as interaction_id, nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'facilitation' AS type
	FROM facilitation_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  links.id as interaction_id, nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'competition' AS type
	FROM competition_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  links.id as interaction_id, nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'parasitic' AS type
	FROM parasitic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
";

	
  // send response headers to the browser
  // following headers instruct the browser to treat the data as a csv file called export.csv
  
 	header( 'Content-Type: text/csv' );
 	header( 'Content-Disposition: attachment;filename=kelpforest_export.csv' );


  $first = true;
  $res = $db->query($sql);
  while ($res->fetchInto($row,DB_FETCHMODE_ASSOC))
  {
  	$row['observations'] = '';
  	$link = $row['interaction_id'];
		$type = $row['type'];
		$obs_table = $row['type']."_interaction_observation";
		unset($row['interaction_id']);
		
  	if ($first) {
  		echocsv(array_keys($row));
  		$first = false;
  	}
		
		$csql = "select c.year, c.id
		from citations c, $obs_table obs
		where (c.id = obs.cite_id) and
		obs.{$type}_interaction_id = ?";
		$obs = $db->getAll($csql,array($link));
		$cites = array();
		
		
		
		foreach ($obs as $observation)
		{

			$asql = "select a.last_name
			from authors a, citations c, author_cite ac
			where (a.id = ac.author_id and ac.cite_id = c.id) and
			c.id = ?";
			$au = $db->getAll($asql,array($observation['id']));
			$cites[] = makecite($observation['year'],$au);			 

		}
		$row['observations'] = implode(', ',$cites);
		  	
  	echocsv($row);
  	
  }
  	
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
?>