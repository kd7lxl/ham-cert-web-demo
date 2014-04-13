<!DOCTYPE html>
<html>
<head>
<title>Amateur Radio Certificate test</title>
<meta name="lang" content="en" />
<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=UTF-8" />
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<link rel="stylesheet" type="text/css" href="css/authtest.css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<h1>Amateur Radio Certificate Authentication Test Page</h1>

<?

// htmlentities() with ENT_QUOTES in UTF-8
function htmlent($s)
{
	return htmlentities($s, ENT_QUOTES, 'UTF-8');
}


function elog($s)
{
	error_log("hamcert-test: $s");
}

function parse_dn($dn)
{
	elog("parse_dn $dn");
	if ($dn[0] == '/') {
	# /1.3.6.1.4.1.12348.1.1=N0CALL/CN=John Doe/emailAddress=jdoe@example.com
		$a = explode('/', $dn);
		if ($a[0] != '') {
			elog("first DN element not empty: $dn");
		} else {
			array_shift($a);
		}
	} else {
	# emailAddress=jdoe@example.com,CN=John Doe,1.3.6.1.4.1.12348.1.1=#13064B44374C584C
		$a = explode(',', $dn);
	}

	if (count($a) < 3) {
		elog("too few elements in DN: $dn");
		return;
	}
	
	$keys = array(
		'1.3.6.1.4.1.12348.1.1' => 'call',
		'CN' => 'name',
		'emailAddress' => 'email'
	);
	
	$e = array();
	for ($i = 0; $i < count($a); $i++) {
		$b = explode('=', $a[$i]);
		if (count($b) != 2) {
			elog("DN element $i does not split nicely with '=': $dn");
			continue;
		}
		
		if (isset($keys[$b[0]])) {
			$e[$keys[$b[0]]] = $b[1];
		} else {
			elog("DN element $b[0] is unsupported: $dn");
		}
	}
	
	# If callsign has an ASN.1 string header, decode as hex.
	# This should be good enough for decoding callsigns despite
	# not being a complete ASN.1 implementation.
	if (substr($e['call'], 0, 5) == '#1306') {
		$e['call'] = pack('H*', substr($e['call'], 5));
	}

	return $e;
}

if (isset($_SERVER['SSL_CLIENT_S_DN'])) {
	$dn_parms = parse_dn($_SERVER['SSL_CLIENT_S_DN']);
	
	if (isset($dn_parms['call']) && isset($dn_parms['name'])) {
		include("cert_ok.php");
	} else {
		include("cert_fail.php");
	}
} else {
	include("cert_none.php");
}

?>

<script src="js/jquery-1.10.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
