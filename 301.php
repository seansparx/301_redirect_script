<?php
		if(md5($_GET['user']) != '58e932e46e6c224537e7bbab1bc42ba6' || md5($_GET['pass']) != '73190a73e21396485654d38bf8aa335c') {
			die("Unauthorize Access.");
		}
		
		if(!file_exists(".htaccess")){
			die(".htaccess does not exists.");
		}
		
		if(!file_exists("htaccess.db")){
			die("htaccess.db does not exists.");
		}

		$token = md5($_SERVER['HTTP_USER_AGENT']."@".$_SERVER['HTTP_HOST']);

		if( isset($_POST['save']) && isset($_POST['from']) && isset($_POST['to']) ) {
			
			if($_POST['token'] != $token) {
				die("Invalid Token.");
			}
			
			// .htaccess Initial Code.
			$htaccess = '
			RewriteEngine on
			';
			
			$count = count($_POST['from']);
			$store = array(); 
			
			for($i = 0; $i<=$count; $i++) {
				
						if ((!filter_var($_POST['from'][$i], FILTER_VALIDATE_URL) === false) && !filter_var($_POST['to'][$i], FILTER_VALIDATE_URL) === false) {
							
							// Databse Variables.
							$store['from'][] = $_POST['from'][$i];
							$store['to'][] 	 = $_POST['to'][$i];
							
							// Htaccess Variables.
							$from = explode("?", $_POST['from'][$i]);
							$to   = $_POST['to'][$i];
							
							// Remove domain name.
							$request_uri = str_replace("http://www.lifecoversearch.com", "", $from[0]);
							$request_uri = str_replace("https://www.lifecoversearch.com", "", $request_uri);
							
							// Query Strings.
							$query_str = $from[1];
							
							// .htaccess Code.
							$htaccess .= '
							RewriteCond   %{REQUEST_URI}    ^'.$request_uri.'$
							RewriteCond   %{QUERY_STRING}   ^'.$query_str.'$
							RewriteRule   ^(.*)$ '.$to.' [R=301,L]
							';
						}
			}
			
			// Append Initial file code.
			$htaccess .= '
			#RewriteRule ^([A-Za-z0-9-]+)/?$ brand.php?cname=$1 [NC,L]';

			file_put_contents("htaccess.db", serialize($store));
			file_put_contents(".htaccess", $htaccess);
		}
		
		$fs_db = unserialize(file_get_contents("htaccess.db"));
?>

<html>
	<head>
		<style>
		table tbody input{ width:80%;}
		</style>
	</head>
	<body>
		<form action="" method="post">
			<table border="0" width="90%">
				<thead>
					<tr><td>Current URL</td><td>Redirect to</td></tr>
				</thead>
				<tbody>
					<?php
						if(isset($fs_db['from']) && count($fs_db['from']) > 0){
							
								for($i = 0; $i <= count($fs_db['from']); $i++) {
									
									if(trim($fs_db['from'][$i])) {
										echo '<tr><td><input type="url" name="from[]" value="'.$fs_db['from'][$i].'"/></td><td><input type="url" name="to[]" value="'.$fs_db['to'][$i].'"/></td></tr>';
									}
								}
						}
					?>
					<tr><td><input type="url" name="from[]" value=""/></td><td><input type="url" name="to[]" value=""/></td></tr>
				</tbody>
				<tfoot>
					<tr><td colspan="2"><center><input name="save" value="submit" type="submit"/></center></td></tr>
				</tfoot>
			</table>
			<input type="hidden" name="token" value="<?php echo $token; ?>"/>
		</form>
	</body>
</html>


<?php
	//echo '<pre>';
	//echo file_get_contents(".htaccess");
	//echo '</pre>';
?>

