<?php
	// load in mysql server configuration (connection string, user/pw, etc)
	include '../db/connect.php';
	// connect to the database
	$con=mysql_connect("$DB_host", "$DB_user", "$DB_pass")or die("cannot connect");
	mysql_select_db("$DB_dbName") or die("cannot select DB");
	
  // run query	
	$sql = "
SELECT  nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'trophic' AS type
	FROM trophic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'facilitation' AS type
	FROM facilitation_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'competition' AS type
	FROM competition_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
	
UNION

SELECT  nodes1.working_name AS node_1_working_name, stages1.name as node1_stage, nodes2.working_name AS node_2_working_name, stages2.name as node2_stage, 'parasitic' AS type
	FROM parasitic_interactions AS links
	LEFT JOIN (stages AS stages1,nodes AS nodes1) ON (stages1.id=links.stage_1_id AND nodes1.id=stages1.node_id)
	LEFT JOIN (stages AS stages2,nodes AS nodes2) ON (stages2.id=links.stage_2_id AND nodes2.id=stages2.node_id)
	WHERE stages1.owner_id != 3 AND stages2.owner_id != 3 AND nodes1.owner_id != 3 AND nodes2.owner_id != 3 and links.owner_id != 3
";
	$result = mysql_query($sql);
	
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
