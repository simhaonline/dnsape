<?php
$domain = $_GET['domain'];
$action = $_GET['action'];
$rectype = $_GET['rectype'];
$domaininfo = $_GET['domaininfo'];
$ip = GetHostByName($domain);
$rand = rand(0,2222);
@mysql_connect('localhost','cotton_ajax','ajaxpw');
mysql_select_db('cotton_ajax');
?>
<?php

// ******* error checking *******
if ($domain=="EnterDomainorIP") {
	echo "Please enter a valid domain name";
	return;
}
$domainhttp = explode('.', $domain);
if ( preg_match( '/http:\/\//i', $domainhttp[0] ) ) {
    echo '<b>Domain names can not contain "http://"...</b>';
    return;
}

// ******* Start DNS Lookup **********
if ($action=="help") {
	include('help.php');
}

// ******* Start DNS Lookup **********
if ($action=="livedns") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
	$resolver->nameservers = array('4.2.2.2');
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');
	echo "<table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\"><div id=\"floatinginfo\"><div id=\"floatinginside\" align=\"center\"><pre><a href=\"javascript:whois();\">".$domain."</a>&nbsp;&nbsp;-&nbsp;<a href=\"javascript:ipwhois();\">".$ip[0].".".$ip[1].".".$ip[2].".".$ip[3]."</a>&nbsp;<br/><b>Reverse DNS:</b>";
        if ($response) {
                foreach ($response->answer as $rr) {
                    $rr->display();
                }
        }
        else {
                echo "No Reverse DNS for ".$domain;
        }
        echo "</pre></div></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table><br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\" >";
	include('library.php');
	$output = Whois_Dig($domain,"ANY");
	echo "<div align=\"center\" style=\"padding-left:20px;text-align:left;\"><pre>$output</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
}

// ******** DNS Traversal *********
if ($action=="traverse") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');         echo "<br />";
$domain_split = explode('.', $domain);
if ($domain_split[0] == 'www') {
    $domain_tld = $domain_split[2];
}
else {
    $domain_tld = $domain_split[1];
}
if ($domain_tld == "com" || $domain_tld == "net") {

	echo "<table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\" align=\"center\"><div class=\"results\"><b>Checking nameservers for ".$domain." on *.gtld-servers.net:</b><br />";

	require_once 'Net/DNS.php';
	
	echo "<pre>";
	$resolver = new Net_DNS_Resolver();
	$resolver->usevc = 1;
	$resolver->nameservers = array('192.5.6.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>a.gtld-servers.net</b> (192.5.6.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	$resolver->nameservers = array('192.33.14.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>b.gtld-servers.net</b> (192.33.14.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	$resolver->nameservers = array('192.26.92.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>c.gtld-servers.net</b> (192.26.92.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.31.80.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>d.gtld-servers.net</b> (192.31.80.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.12.94.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>e.gtld-servers.net</b> (192.12.94.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.35.51.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>f.gtld-servers.net</b> (192.35.51.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.42.93.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>g.gtld-servers.net</b> (192.42.93.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.54.112.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>h.gtld-servers.net</b> (192.54.112.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.43.172.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>i.gtld-servers.net</b> (192.43.172.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.48.79.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>j.gtld-servers.net</b> (192.48.79.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.52.178.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>k.gtld-servers.net</b> (192.52.178.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}

	$resolver->nameservers = array('192.41.162.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>l.gtld-servers.net</b> (192.41.162.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}

	$resolver->nameservers = array('192.55.83.30');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>m.gtld-servers.net</b> (192.55.83.30):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	echo "</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
}
elseif ($domain_tld != "com" || $domain_tld != "net") {

	echo "<div class=\"results\"><font size=\"4\"><b>".$domain.": DNS Traversal</b></font><br /><br /><b>.us/.biz/.org Traversals coming soon</b></div>";

// Checking nameservers for ".$domain." on .org tld servers:</b><br />";

	require_once 'Net/DNS.php';
	
	echo "<pre>";
	$resolver = new Net_DNS_Resolver();
	$resolver->usevc = 1;
	$resolver->nameservers = array('204.74.112.1');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld1.ultradns.net</b> (204.74.112.1):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	$resolver->nameservers = array('204.74.113.1');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld2.ultradns.net</b> (204.74.113.1):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	$resolver->nameservers = array('199.7.66.1');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld3.ultradns.org</b> (199.7.66.1):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('199.7.67.1');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld4.ultradns.org</b> (199.7.67.1):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('192.100.59.11');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld5.ultradns.info</b> (192.100.59.11):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
	$resolver->nameservers = array('198.133.199.11');
	$response = $resolver->query($domain, 'NS');
	if ($response) {
		echo "Response from <b>tld6.ultradns.co.uk</b> (198.133.199.11):<br />";
		foreach ($response->answer as $rr) {
			$rr->display();
		}
	}
	
}
	// include 'http://www.dnsstuff.com/tools/traversal.ch?domain='.$domain.'&type=A';
}

// ******** HTTP Headers ********
if ($action=="headers") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');         echo "<br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\">";
	include('library.php');
	echo "<div class=\"results\"><b>Returned HTTP Headers for ".$domain."</b>:";
	echo Whois_HTTPHeader($domain);
	echo "</div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
}

// ********* Whois **********
if ($action=="whois") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');
        echo "<br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\">";
$domain = explode('.', $domain);
if ($domain[0] == 'www') {
    $domain = $domain[1].'.'.$domain[2];
}
else {
	$domain = $_GET['domain'];
}
include('lib.php');
$defaultdomain = "DELL.COM";

$type = (isset($_REQUEST['type'])) ? strtoupper($_REQUEST['type']) : "";
$ripe = (isset($_REQUEST['ripe'])) ? strtoupper($_REQUEST['ripe']) : "216.69.191.1";
$arin = (isset($_REQUEST['arin'])) ? strtoupper($_REQUEST['arin']) : "216.69.191.1";

$submit = (isset($_REQUEST['submit'])) ? $_REQUEST['submit'] : "Lookup";

if (!isset($_REQUEST['submit']))
	$addtohistory = "ON";
else
	$addtohistory = (isset($_REQUEST['addtohistory'])) ? strtoupper($_REQUEST['addtohistory']) : "OFF";

$addtohistory = ($addtohistory == "ON") ? TRUE : FALSE;


$result = "";
$whois = "";

$PageExists = FALSE;

if ($submit == "Lookup")
	{
	$whois = Whois_FindServer($domain);
	$data = Whois_Query($type.$domain, $whois);

	if (strpos($data, "To single out one record") !== FALSE)
		$data = Whois_Query("DOMAIN ".$domain, $whois);
	}
	
if (strpos($data, "No match for") !== FALSE){
	echo "No results in the Whois Database for <b>".$domain."</b>.";
}
if ($data != FALSE)
{

	if ($submit == "Lookup")
		{
		$datab = strpos($data, "Domain Name:");
		$datax = strrpos(substr($data, 0, $datab), ".");

		if ($datax != 0)
			{
			for (; $data[$datax] != " " && $data[$datax] != 0; $datax++);
			$datax++;
			}

		$extension = substr($domain, strpos($domain, ".") + 1);

		if (strcasecmp($extension, "com") == 0 || strcasecmp($extension, "net") == 0 || strcasecmp($extension, "org") == 0)
			$datax++;

		if (strpos($data, ">>>") != FALSE)
			$data = substr($data, $datax, strpos($data, ">>>") - 2 - $datax);
		else
			$data = substr($data, $datax);	
		}

	$DomainInfo = Whois_GetDomainInfo($data);

        if (($DomainInfo['taken'] == TRUE) && (strpos($data, "Domain Name") !== FALSE))
        	{

		$data = str_replace("Name Server: \r\n", "", $data);
		$data = str_replace("\r\n\r\n", "", $data);

			$data = str_replace("REGISTRAR-LOCK", "<font color=\"red\"><b>REGISTRAR-LOCK</b></font>", $data);
			$data = str_replace("clientDeleteProhibited", "<font color=\"red\"><b>clientDeleteProhibited</b></font>", $data);
			$data = str_replace("clientTransferProhibited", "<font color=\"red\"><b>clientTransferProhibited</b></font>", $data);
			$data = str_replace("ACTIVE", "<font color=\"green\"><b>ACTIVE</b></font>", $data);

			echo "<div class=\"results\"><b>Registry results for $domain:</b>";

		echo "<pre>";
		echo $data;
		echo "</pre></div>";

		$deepdata = "";

		if ($DomainInfo['server'] != FALSE)
			{
			echo "<div class=\"results\"><b>Registrar results for $domain:</b>";
			echo "<pre>";

			$deepdata = Whois_Query($domain, $DomainInfo['server']);
			$deepdata = substr($deepdata, strpos($deepdata, "Registrant:"));

			if (strpos($deepdata, "The data in this whois database") !== FALSE)
				$deepdata = substr($deepdata, 0, strpos($deepdata, "The data in this whois database"));

			$tok = strtok($deepdata, "\n");

			while($tok)
				{
				$line = wordwrap($tok, 85, "\r\n");
				if ((strpos($line, "\r") === FALSE) && (strpos($line, "\n") === FALSE))
					$line .= "\r\n";
				echo $line;
				$tok = strtok("\n");
				}

			echo "</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
			}
		}
	}
}

// ********* IP Whois **********
if ($action=="ipwho") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');
        echo "<br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\">";

	include_once('whois/whois.main.php');
	include_once('whois/whois.utils.php');
	
	$ip = GetHostByName($domain);
	$whois = new Whois();
	$result = $whois->Lookup($ip);
	echo "<div class=\"results\"><b>Whois results for $ip :</b>";
	$utils = new utils;
	echo "<pre>";
	echo $utils->showHTML($result);
	echo "</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
}

// ******** RBL Search ********
if ($action=="rbl") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');
        echo "<br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\" >";
echo "<div class=\"results\"><pre>";
$domain = explode('.', $domain);
if ($domain[0] == 'www') {
    $domain = $domain[1].'.'.$domain[2];
}
else {
        $domain = $_GET['domain'];
}


	require_once 'Net/DNSBL.php';
	$dnsbl = new Net_DNSBL();
	$remoteIP = GetHostByName($domain); 
	$ip = GetHostByName($domain);
	$blacklists = array('sbl-xbl.spamhaus.org', 
						'bl.spamcop.net', 
						'cbl.abuseat.org', 
						'dnsbl.njabl.org', 
						'relays.ordb.org', 
						'dialups.mail-abuse.org',
						'relays.mail-abuse.org',
						'nonconfirm.mail-abuse.org',
						'whois.rfc-ignorant.org',
						'rhsbl.sorbs.net',
						'dnsbl.sorbs.net');
	for($j=0; $j<=10; $j++){
		$blacklist = $blacklists[$j];
		$dnsbl->setBlacklists(array($blacklist));
		echo "<table><tr><td><b>RBL results from ".$blacklist.":</b></td><td>";
		if ($dnsbl->isListed($remoteIP)) {
			echo "<td>&nbsp;&nbsp;".$domain." (".$ip.")</td><td>&nbsp;<em><font color=\"red\">is listed</font></em>.</td></tr></table>";
		}
		else {
			echo "<td>&nbsp;&nbsp;".$domain." (".$ip.")</td><td>&nbsp;<em><font color=\"green\">is not listed</font></em>.</td></tr></table>";
		}
	}
	echo "</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>
";
}

// *********** Trace Route *************
if ($action=="tracert") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
	echo "<div id=\"busy\"><div style=\"float:left;\"><img src=\"images/ajax.png\" height=\"101\" width=\"116\" /></div><div><table><tr><td><img src=\"images/lfarrow.gif\" /></td></tr><tr><td><img src=\"images/rtarrow.gif\" /></td></tr></table></div><div style=\"float:right;\"><img src=\"images/server.p</div></div>";
echo "<font size=\"4\"><b>".$domain.": Traceroute</b></font><br /><br />";

// $output = shell_exec("traceroute ".$domain);
echo "<b>Route Trace for ".$domain."</b>:<br /><pre>$output</pre>";
}

// ********* Ping **************
if ($action=="ping") {
	@mysql_query("insert into queries (domainorip,user,action,timestamp) values ('$domain','".GetHostByName($REMOTE_ADDR)."','$action',FROM_UNIXTIME(".time()."))");
        // Reverse DNS
        include("Net/DNS.php");
        $resolver = new Net_DNS_Resolver();
        $ip = explode(".", $ip);
        $rev = $ip[3].".".$ip[2].".".$ip[1].".".$ip[0].".in-addr.arpa";
        $response = $resolver->query($rev, 'PTR');
        echo "<br /><table cellpadding=\"0\" cellspacing=\"0\" align=\"center\"><tr><td class=\"tl\"></td><td class=\"top\"></td><td class=\"tr\"></td></tr><tr><td class=\"left\"></td><td class=\"nav\" align=\"center\">";
	$output = shell_exec("ping -c 4 ".$domain);
	echo "<div class=\"results\"><b>Pinging ".$domain."</b>:<br /><pre>$output</pre></div></td><td class=\"right\"></td></tr><tr><td class=\"bl\"></td><td class=\"bottom\"></td><td class=\"br\"></td></tr></table>";
}

// ******** Contact Form *********
if ($action=="contact") {
	echo "<div class=\"results\"><div id=\"title\" align=\"center\"><font size=\"4\"><b>Contact Ajax DNS</b></font></div>";
	echo "<span id=\"label\">Name:</span><input id=\"name\" style=\"width:100px\" type=\"text\" /><br />";
	echo "<span id=\"label\">Email:</span><input id=\"email\" style=\"width:100px\" type=\"text\" /><br />";
	echo "<span id=\"label\">Comments:</span><input id=\"comments\" style=\"width:100px; height:50px\" type=\"text\" /><br />";
	echo "<span id=\"label\"></span><input type=\"button\" value=\"Send\" onclick=\"submitform();\" /></div>"; 
}
?>
