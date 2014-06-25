<?php
	// load in mysql server configuration (connection string, user/pw, etc)
	include '../db/connect.php';
	// connect to the database
	$con=mysql_connect("$DB_host", "$DB_user", "$DB_pass")or die("cannot connect");
	mysql_select_db("$DB_dbName") or die("cannot select DB");
	
  // run query	
	$sql = "
SELECT nodes.id, working_name, name as function_group, native_status, is_assemblage
FROM nodes, functional_groups
WHERE nodes.functional_group_id=functional_groups.id";
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
