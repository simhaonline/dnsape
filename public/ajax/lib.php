<?php

//function Whois_Query($Domain, $Server)
//function Whois_FindServer($Domain)
//function Whois_Dig($HostName)
//function Whois_Ping($HostName) 
//function Whois_TraceRoute($HostName, $Hops)
//function Whois_IP($IPAddress)
//function Whois_GetDomainInfo($Data)
//function Whois_GetAdminEmail($Data)
//function Whois_ConvertTime($Time)
//function Whois_GetParameter($Whois, $Parameter)
//function Whois_AddToRecentQueries($Domain, $DomainTaken, $PageExists) 
//function Whois_DisplayRecentQueries($NumberOfQueries) 

//function File_Read($URL)
//function File_Write($URL, $Data, $Access = 'w+')

//function HTTP_GetHeaders($Domain)
//function HTTP_GetHeaderParameter($Header, $Parameter)

//function HTML_BuildURL($FullURL, $PartURL)

//function Text_WordWrap($Text)

//function Directory_ReadContents($Path)

function GetDatabaseConnection()
{
	$DatabaseServer		= "localhost";
	$DatabaseUsername	= "nathanm_whoisdat";
	$DatabasePassword	= "whoisdat";
	$DatabaseName		= "nathanm_whoisdat";

	$Handle = mysql_connect($DatabaseServer, $DatabaseUsername, $DatabasePassword);

	if ($Handle == FALSE)
		echo "Cannot connect to the database.<BR>";
	
	if (mysql_select_db($DatabaseName) == FALSE)
		echo "Cannot open database.<BR>";

	return $Handle;
}

function Whois_Query($Domain, $Server)
{
	$SocketHandle = fsockopen($Server, 43, $ErrorNumber, $ErrorString, 2);
	
	if ($SocketHandle == FALSE) 
		{
		//echo "ERROR: $ErrorNumber - $ErrorString<br>\n";
		return FALSE;
		} 
	
	fwrite($SocketHandle, $Domain."\r\n");
	
	$Out = $Read = fread($SocketHandle, 512);
	while(strlen($Read) != 0)
		$Out .= $Read = fread($SocketHandle, 512);
		
	fclose($SocketHandle);
	return $Out;
}

function Whois_FindServer($Domain)
{
	$Server		= "whois.internic.net";
	$ServerList = File_Read("servers.txt");
	
	$DomainTLD = strtolower(substr($Domain, strpos($Domain, ".") + 1));

	$ServerToken = strtok($ServerList, "\n");

	while($ServerToken)
		{
		if ($ServerToken[0] != "#")
			{
			$ServerTLD = substr($ServerToken, 0, strpos($ServerToken, "|"));

			$Start = strpos($ServerToken, "|") + 1;
			$End = strpos($ServerToken, "|", $Start);
			$WhoisServer = substr($ServerToken, $Start, $End - $Start);

			if (strcasecmp($DomainTLD, $ServerTLD) == 0)
				{
				$Server = trim($WhoisServer);
				break;
				}
			}

		$ServerToken = strtok("\n");
		}
	
	return $Server;
}

function Whois_Dig($HostName) 
{ 
	$Records[100][10] = null;
	$RecordCount = 0;
	
	if(empty($HostName)) 
		return "";

	exec("dig $HostName A $HostName CNAME $HostName NS $HostName MX $HostName ALL", $Result);

	if (strpos($Result[1], "DiG 8.3") !== FALSE)
		exec("dig $HostName", $Result);

	if (count($Result) == 0)
		return "";
	
	//echo "<pre>"; print_r($Result); echo "</pre>";
	
	foreach ($Result as $RecordLine) 
		{ 
		if ($RecordLine != "" && $RecordLine[0] != ';')
			{
			$RecordLine = str_replace(" ", "\t", $RecordLine);
			$RecordLine = str_replace("\0", "\t", $RecordLine);
			$RecordLine = str_replace("0\t", "0 \t", $RecordLine);
			$RecordLine = str_replace("\t\t", "\t", $RecordLine);
			
			$AddRecord = TRUE;
			
			$NewRecord[0] = $RecordLine;
	
			$Item = strtok($RecordLine, "\t");
			
			for($i = 1; $Item; $i++)
				{
				$NewRecord[$i] = $Item;
				
				if ($Item == "MX")
					$NewRecord[$i] .= strtok("\t");

				$Item  = strtok("\t");
				}
				
			for($i = 0; $i != count($Records); $i++)
				{
					if (isset($Records[$i]))
						{
						if (($Records[$i][1] == $NewRecord[1]) &&
							($Records[$i][4] == $NewRecord[4]) && 
							($Records[$i][5] == $NewRecord[5]))
							$AddRecord = FALSE;
						
						$RecordClass = strtoupper($NewRecord[3]);

						if ($RecordClass == "RETRY" || $RecordClass == "EXPIRY" ||
							$RecordClass == "SERIAL" || $RecordClass == "REFRESH")
							$AddRecord = FALSE;
						}
				}
				
			if ($AddRecord == TRUE)
				{
				for ($i = 0; $i != count($NewRecord); $i++)
					$Records[$RecordCount][$i] = $NewRecord[$i];
				$RecordCount++;
				}
			}

		
		}    

	

	if ((count($Records) <= 1) || ($RecordCount == 1))
		{
		$NoRecords = "<table width=100% border=0 cellspacing=0 cellpadding=3>";

		$NoRecords .= "<tr id=bluebar>";
		$NoRecords .= "<td colspan=5><b><font face=verdana size=1 color=white>Dig - ".strtoupper($HostName)."</td>";
		$NoRecords .= "</tr>";

		$NoRecords .= "<tr id=orangebar>";
		$NoRecords .= "<td><font face=verdana size=1 color=white>No Records Found</td>";
		$NoRecords .= "</tr>";
		$NoRecords .= "</table>";

		return $NoRecords;
		}

	$Text = "<table width=100% border=0 cellspacing=0 cellpadding=3>\n";
	
	$Text .= "<tr id=bluebar>\n";
	$Text .= "<td colspan=5><b><font face=verdana size=1 color=white>Dig - ".strtoupper($HostName)."</td>\n";
	$Text .= "</tr>\n";
	
	$Text .= "<tr id=orangebar>\n";
	$Text .= "<td width=150><font face=verdana size=1 color=white>Record Name</td>\n";
	$Text .= "<td width=35><font face=verdana size=1 color=white>TTL</td>\n";
	$Text .= "<td width=35><font face=verdana size=1 color=white>Class</td>\n";
	$Text .= "<td width=45><font face=verdana size=1 color=white>Type</td>\n";
	$Text .= "<td width=200><font face=verdana size=1 color=white>Points To</td>\n";
	$Text .= "</tr>\n";

	$SortedRecords[] = NULL;
	foreach($Records as $Row)
		if (isset($Row[4]))
			$SortedRecords[] = $Row[4];

	array_multisort($SortedRecords, SORT_ASC, $Records);

	//echo "<pre>"; print_r($Records); echo "</pre>";
	
	for ($i = 1; $i < $RecordCount; $i++)
		{
		if (isset($Records[$i]) && isset($Records[$i][0]))
			{
			$Text .= "<tr";
			
			if ($Records[$i][4] == "A")      				$Text .= " id=rec1";
			if ($Records[$i][4] == "CNAME")   				$Text .= " id=rec2";
			if (strpos($Records[$i][4], "MX") !== FALSE)                    $Text .= " id=rec2";
			if ($Records[$i][4] == "NS")  	 				$Text .= " id=rec3";
			if ($Records[$i][4] == "ANY")    				$Text .= " id=rec4";
			if ($Records[$i][4] == "SOA")    				$Text .= " id=rec4";

			$Text .= ">\n";
			
			for ($x = 1; $x < 6; $x++)
				{
				if ($Records[$i][$x] != "" && $Records[$i][$x][strlen($Records[$i][$x]) - 1] == ".")
					$Records[$i][$x][strlen($Records[$i][$x]) - 1] = "";
				if (($x == 1) && ($Records[$i][1] == "" || strlen($Records[$i][$x]) == 1))
					$Records[$i][1] = $HostName;

				//echo $x." ".$Records[$i][$x]."".strlen($Records[$i][$x]);
				//printf("%d", $Records[$i][$x][0]);
				//echo "<br>";

				if (($x == 5) && ($Records[$i][4] == "A"))
					$Text .= "<td><font face='arial' size=1><a href=ipwhois.php?arin=".$Records[$i][$x]." style='color: #000000;'>".strtoupper($Records[$i][$x])."</a></td>\n";
				else
					$Text .= "<td><font face='arial' size=1>".strtoupper($Records[$i][$x])."</td>\n";
				}
				
			$Text .= "</tr>\n";
			}
		}

	$Text .= "</table>\n";

	return $Text;
}

function Whois_Ping($HostName) 
{ 
	$Records[100][10] = null;
	$RecordCount = 0;
	
	if(empty($HostName)) 
		return "";

	exec("ping $HostName -c 4 -W 1 -s 10 -a", $Result);

	if (count($Result) < 2)
		{
		$NoRecords = "\n<table width=100% border=0 cellspacing=0 cellpadding=3>\n";

		$NoRecords .= "<tr id=bluebar>";
		$NoRecords .= "<td colspan=5><b><font face=verdana size=1 color=white>Ping - ".strtoupper($HostName)."</td>";
		$NoRecords .= "</tr>";

		$NoRecords .= "<tr id=orangebar>";
		$NoRecords .= "<td><font face=verdana size=1 color=white>Server Not Responding</td>";
		$NoRecords .= "</tr>";
		$NoRecords .= "</table>";

		return $NoRecords;
		}

	$Text = "<table width=100% border=0 cellspacing=0 cellpadding=3>\n";

	$Text .= "<tr id=bluebar>\n";
	$Text .= "<td colspan=6><b><font face=verdana size=1 color=white>Ping - ".strtoupper($HostName)."</td>\n";
	$Text .= "</tr>\n";
	
	//echo "<pre>";
	//print_r($Result);
	//echo "</pre>";

	$RecordCount = 0;
	for ($i = 0; $i != count($Result); $i++)
		if (strpos($Result[$i], "icmp_seq=") !== FALSE)
			$RecordCount++;
	
	if ($RecordCount != 0)
		{
		$Text .= "<tr id=orangebar>\n";
		$Text .= "<td><font face=verdana size=1 color=white>Bytes Sent</td>\n";
		$Text .= "<td><font face=verdana size=1 color=white>Sent To</td>\n";
		$Text .= "<td><font face=verdana size=1 color=white>IP Address</td>\n";
		$Text .= "<td><font face=verdana size=1 color=white>ICMP Seq</td>\n";
		$Text .= "<td><font face=verdana size=1 color=white>TTL</td>\n";
		$Text .= "<td><font face=verdana size=1 color=white>Total Time (MS)</td>\n";
		$Text .= "</tr>\n";
		}

	for ($i = 0; $i != count($Result); $i++)
		{
		if (strpos($Result[$i], "icmp_seq=") !== FALSE)
			{
			$Result[$i] = str_replace("(", "", $Result[$i]);
			$Result[$i] = str_replace(")", "", $Result[$i]);
			$Result[$i] = str_replace(":", "", $Result[$i]);
			$Result[$i] = str_replace("ttl=", "", $Result[$i]);	
			$Result[$i] = str_replace("icmp_seq=", "", $Result[$i]);
			$Result[$i] = str_replace("time=", "", $Result[$i]);
			$Result[$i] = str_replace("MS", "", $Result[$i]);
			$Result[$i] = str_replace("ms", "", $Result[$i]);
			$Result[$i] = str_replace("\r", "", $Result[$i]);
			$Result[$i] = str_replace("\n", "", $Result[$i]);
			$Result[$i] = str_replace("\r\n", "", $Result[$i]);
			$Result[$i] = str_replace(0, "", $Result[$i]);
			$Result[$i] = trim($Result[$i]);

			$PingToken = strtok($Result[$i], " ");

			$Text .= "<tr id=tanbk>\n";

			for($c = 0; ($c != 6) && ($PingToken !== FALSE); )
				{
				if ( ($PingToken != "bytes") && ($PingToken != "from"))
					{
					$Text .= "<td><font face=verdana size=1>".strtoupper($PingToken)."</td>\n";
					$c++;
					}
				$PingToken = strtok(" ");
				}

			$Text .= "</tr>\n";
			}

		if (strpos($Result[$i], "ping statistics") !== FALSE)
			{
			$Text .= "<tr id=orangebar>\n";
			$Text .= "<td><font face=verdana size=1 color=white>Packets Sent</td>\n";
			$Text .= "<td><font face=verdana size=1 color=white>Recieved</td>\n";
			$Text .= "<td><font face=verdana size=1 color=white>Packet Loss</td>\n";
			$Text .= "<td><font face=verdana size=1 color=white>Time (MS)</td>\n";
			$Text .= "<td></td>\n";
			$Text .= "<td></td>\n";
			$Text .= "</tr>\n";

			$i++;

			$Result[$i] = str_replace("packets transmitted", "", $Result[$i]);
			$Result[$i] = str_replace("received", "", $Result[$i]);
			$Result[$i] = str_replace("packet loss", "", $Result[$i]);
			$Result[$i] = str_replace("time ", "", $Result[$i]);	
			$Result[$i] = str_replace("ms", "", $Result[$i]);
			$Result[$i] = str_replace(" ", "", $Result[$i]);	
			$Result[$i] = str_replace(",", " ", $Result[$i]);

			$Text .= "<tr id=tanbk>";

			$PingToken = strtok($Result[$i], " ");
		
			while($PingToken !== FALSE)
				{
				$Text .= "<td><font face=verdana size=1>".strtoupper($PingToken)."</td>\n";
				$PingToken = strtok(" ");
				}
			
			$Text .= "<td></td>\n";
			$Text .= "<td></td>\n";
			$Text .= "</tr>\n";
			}	
		}

	$Text .= "</table>\n";

	return $Text;
}

function Whois_TraceRoute($HostName, $Hops) 
{ 
	if(empty($HostName)) 
		return "";
	
	//ob_start("callback");

	echo "<pre style='font-family: verdana; font-size: 8pt;'>";
 	exec("traceroute $HostName -w 2 -m $Hops", $Result);
	echo "</pre>";

	//$page = ob_get_contents();
 	//ob_end_flush();
	//echo $page;

	echo "<table width=100% border=0 cellspacing=0 cellpadding=3>\n";

	echo "<tr id=bluebar>\n";
	echo "<td colspan=7><b><font face=verdana size=1 color=white>TraceRoute (".$Hops." HOPS MAX) - ".strtoupper($HostName)."</td>\n";
	echo "</tr>\n";
	
	echo "<tr id=orangebar>\n";
	echo "<td><font face=verdana size=1 color=white>Hop</td>\n";
	echo "<td><font face=verdana size=1 color=white>Address</td>\n";
	echo "<td><font face=verdana size=1 color=white>Organization</td>\n";
	echo "<td><font face=verdana size=1 color=white>IP Address</td>\n";
	echo "<td><font face=verdana size=1 color=white>Max (MS)</td>\n";
	echo "<td><font face=verdana size=1 color=white>Avg (MS)</td>\n";
	echo "<td><font face=verdana size=1 color=white>Min (MS)</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	
	//print_r($Result);

	for ($i = 0; $i != count($Result); $i++)
		{
		echo "<table width=100% border=0 cellspacing=0 cellpadding=3>\n";
		echo "<tr id=tanbk>";

		$Result[$i] = str_replace(" ms", "", $Result[$i]);
		$Result[$i] = str_replace(")", "", $Result[$i]);
		$Result[$i] = str_replace("(", "", $Result[$i]);
		$Result[$i] = str_replace("* * *", "* * * * *", $Result[$i]);

		//echo $Result[$i]."<br>";

		$TraceToken = strtok($Result[$i], " ");

		for($x = 0; $TraceToken !== FALSE; $x++)
			{
			$Org = "*";
			if ($TraceToken != "*")
				$Org = Whois_GetParameter(Whois_IP($TraceToken), "OrgName");

			if ($x == 2)
				echo "<td width=100><font face=verdana size=1>".$Org."</td>";

			echo "<td width=100><font face=verdana size=1>".strtoupper($TraceToken)."</td>\n";

			$TraceToken = strtok(" ");
			}

		echo "</tr>\n";
		echo "</table>\n";
		}

	echo "</table>\n";

	return $Text;
}

function Whois_IP($IPAddress)
{
	$Whois = "whois.arin.net";
	$Data = Whois_Query($IPAddress, $Whois);

	while ($Data == FALSE)
		$Data = Whois_Query($IPAddress, $Whois);

	//echo "Data: ".$Data."<br>";

	$Text  = "<table width=600 border=0 cellspacing=0 cellpadding=0><tr><td valign=top>";
	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	
	$Text .= "<tr bgcolor=#3366CC>";
	$Text .= "<td colspan=6><b><font face=verdana size=1 color=white>ARIN Whois</td>";
	$Text .= "</tr>";

	$Text .= "<tr bgcolor=#FDF5E6>";
	$Text .= "<td colspan=6>";
	$Text .= "<font face=verdana size=1 color=#EEAA2D>";
	$Text .= "<b>WHOIS INFORMATION (".strtoupper($Whois)." - ".$IPAddress."):</b></FONT><font face=verdana size=2>";
	$Text .= "<pre>";
	$Text .= $Data;
	$Text .= "</pre>";
	$Text .= "</font>";
	$Text .= "</td>";
	$Text .= "</tr>";
	$Text .= "</table>";

	$Text .= "</td></tr></table>";

	return $Text;
}

function Whois_HTTPHeader($Domain)
{
	$Text  = "<table width=600 border=0 cellspacing=0 cellpadding=0><tr><td valign=top>";
	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	
	$Text .= "<tr bgcolor=#3366CC>";
	$Text .= "<td colspan=6><b><font face=verdana size=1 color=white>HTTP Headers (".strtoupper($Domain).")</td>";
	$Text .= "</tr>";

	$Text .= "<tr bgcolor=#FDF5E6>";
	$Text .= "<td colspan=6>";
	
	$Text .= "<font face='Courier New' style='font-size: 8pt;' color=#000000>";
	//$Text .= "<pre>";
	
	$Location = $Domain;
	$PageLocation = $Location;

	while($Location != "")
		{
		$Headers = HTTP_GetHeaders($Location);
	
		$Token = strtok($Headers, "\r\n");
			
		while($Token)
			{
			$Text .= Text_WordWrap($Token, "\r")."<br>";
			$Token = strtok("\r\n");
			}

		$Text .= "<br>";

		$Location = HTTP_GetHeaderParameter($Headers, "Location: ");
		
		if ($Location != "")
			{
			$PageLocation = HTML_BuildURL($PageLocation, $Location);
			$Text .= "<b>Redirecting to ".$PageLocation."</b><br><br>";
			}
		}

	//$Text .= "</pre>";
	$Text .= "</font>";
	$Text .= "</td>";
	$Text .= "</tr>";
	$Text .= "</table>";

	$Page = HTTP_GetPage($PageLocation);

	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	
	$Text .= "<tr bgcolor=#3366CC>";
	$Text .= "<td colspan=6><b><font face=verdana size=1 color=white>Page Images</td>";
	$Text .= "</tr>";

	$Text .= "<tr bgcolor=#FDF5E6>";
	$Text .= "<td colspan=6>";
	$Text .= "<font face='Courier New' style='font-size: 8pt;' color=#000000>";

	$Text .= Parse($PageLocation, $Page, 1);

	$Text .= "</font>";
	$Text .= "</td></tr>";
	$Text .= "</table>";


	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	$Text .= "<tr bgcolor=#3366CC><td colspan=6><b><font face=verdana size=1 color=white>Page Links</td></tr>";
	$Text .= "<tr bgcolor=#FDF5E6><td colspan=6>";

	$Text .= "<font face='Courier New' style='font-size: 8pt;' color=#000000>";

	$Text .= Parse($PageLocation, $Page, 3);

	$Text .= "</font>";
	$Text .= "</td></tr>";
	$Text .= "</table>";


	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	$Text .= "<tr bgcolor=#3366CC><td colspan=6><b><font face=verdana size=1 color=white>Page Text</td></tr>";
	$Text .= "<tr bgcolor=#FDF5E6><td colspan=6>";

	$Text .= "<font face='Courier New' style='font-size: 8pt;' color=#000000>";

	$PageText = Parse($PageLocation, $Page, 2);

	$PageText = str_replace("  ", " ", $PageText);
	$PageText = str_replace("&nbsp;", " ", $PageText);
	$PageText = str_replace("&nbsp;", " ", $PageText);
	$PageText = str_replace("\n", "", $PageText);
	$PageText = str_replace("\r", "", $PageText);
	$PageText = str_replace("\t", "", $PageText);

	$PageText = HTML_DecodeSpecialChars($PageText);

	$Text .= Text_WordWrap($PageText, "", "<br>\n", 82);

	$Text .= "</font>";
	$Text .= "</td></tr>";
	$Text .= "</table>";

	$Text .= "<table width=100% border=0 cellspacing=0 cellpadding=3>";
	
	$Text .= "<tr bgcolor=#3366CC>";
	$Text .= "<td colspan=6><b><font face=verdana size=1 color=white>Page Contents</td>";
	$Text .= "</tr>";
	
	$Text .= "<tr bgcolor=#FDF5E6>";
	$Text .= "<td colspan=6>";
	$Text .= "<font face='Courier New' style='font-size: 8pt;' color=#000000>";

	$Text .= "<xmp>";

	$Page = str_replace("\t", "", $Page);
	$Page = str_replace("\r", "", $Page);
	$Page = str_replace("\n", "", $Page);
	$Page = str_replace("  ", "", $Page);

	//$Token = strtok($Page, "\r");

	//while($Token)
		//{
		$Text .= Text_WordWrap($Page, ">");
		//$Text .= Text_WordWrap(str_replace("\t", "", $Token));
		//$Token = strtok("\r");
		//}

	$Text .= "</xmp>";
	$Text .= "</font>";
	$Text .= "</td></tr>";
	$Text .= "</table>";


	$Text .= "</td></tr></table>";

	return $Text;
}

function Whois_GetDomainInfo($Data)
{	
	$DomainTaken = TRUE;

	if (strpos($Data, "NOT FOUND") !== FALSE) // .org
		$DomainTaken = FALSE;	
	if (strpos($Data, "Not found") !== FALSE) // .biz, .us
		$DomainTaken = FALSE;	
	if (strpos($Data, "No match") !== FALSE)  // .com,.co.uk
		$DomainTaken = FALSE;

	if ($DomainTaken == TRUE)
	{

	$Domain  = Whois_GetParameter($Data, "Domain Name");	
	$Status  = Whois_GetParameter($Data, "Status");
	$Expires = Whois_GetParameter($Data, "Expiration Date");
	$Created = Whois_GetParameter($Data, "Creation Date");
	$Updated = Whois_GetParameter($Data, "Updated Date");
	$Registrar = Whois_GetParameter($Data, "Registrar");
	$RecordStart = substr($Data, strpos($Data, "Domain Name:"));
	$Server = strtoupper(Whois_GetParameter($RecordStart, "Whois Server"));
	$Locked = FALSE;

	if ($Status == FALSE)
		$Status = Whois_GetParameter($Data, "Domain Status");  //.biz,.us

	$Status = str_replace("CLIENT", "", strtoupper($Status));
	$Status = str_replace(".", "", strtoupper($Status));

	if ($Expires == FALSE)
		$Expires = Whois_GetParameter($Data, "Domain Expiration Date");  //.biz,.us
	if ($Expires == FALSE)
		$Expires = Whois_GetParameter($Data, "Renewal Date");  //.co.uk

	if ($Created == FALSE)
		$Created = Whois_GetParameter($Data, "Created On"); //.org
	if ($Created == FALSE)
		$Created = Whois_GetParameter($Data, "Domain Registration Date");  //.biz,.us
	if ($Created == FALSE)
		$Created = Whois_GetParameter($Data, "Domain created on"); // .ws
	if ($Created == FALSE)
		$Created = Whois_GetParameter($Data, "Registered on"); // .co.uk

	if ($Updated == FALSE)
		$Updated = Whois_GetParameter($Data, "Last Updated On"); //.org
	if ($Updated == FALSE)
		$Updated = Whois_GetParameter($Data, "Domain Last Updated Date"); //.biz,.us
	if ($Updated == FALSE)
		$Updated = Whois_GetParameter($Data, "Domain last updated on"); //.ws
	if ($Updated == FALSE)
		$Updated = Whois_GetParameter($Data, "Last updated"); //.co.uk

	if ($Created) $Created = Whois_ConvertTime($Created);
	if ($Updated) $Updated = Whois_ConvertTime($Updated);
	if ($Expires) $Expires = Whois_ConvertTime($Expires);

	if ((strpos($Data, "REGISTRAR-LOCK") !== FALSE) || (strpos($Data, "REGISTRAR-HOLD") !== FALSE))
		$Locked = TRUE;
	
	$Nameserver = "";
	$NameserverCount = 0;
	$NSData = $Data;

	while($Nameserver !== FALSE)
		{
		$Nameserver = Whois_GetParameter($NSData, "Name Server");
			
		if (($Nameserver === FALSE) || ($Nameserver == ""))
			break;

		$DomainInfo['nameservers'][$NameserverCount] = $Nameserver;

		$NSFind = strpos($NSData, $Nameserver);
		$NSData = substr($NSData, $NSFind + strlen($Nameserver) + 1);

		$NameserverCount++;
		}

	$DomainInfo['domain'] = strtoupper($Domain);
	$DomainInfo['locked'] = $Locked;
	$DomainInfo['server'] = strtoupper($Server);
	$DomainInfo['status'] = strtoupper($Status);
	$DomainInfo['created'] = strtoupper($Created);
	$DomainInfo['updated'] = strtoupper($Updated);
	$DomainInfo['expires'] = strtoupper($Expires);
	$DomainInfo['registrar'] = strtoupper($Registrar);
	}

	$DomainInfo['taken'] = $DomainTaken;

	return $DomainInfo;
}

function Whois_GetAdminEmail($Data)
{
	$AdminEmail = Whois_GetParameter($Data, "Administrative Contact Email:"); //.us,.biz

	if ($AdminEmail == FALSE)
		$AdminEmail = Whois_GetParameter($Data, "Admin-Mailbox:"); //.ca
	
	if ($AdminEmail == FALSE)
		$AdminEmail = Whois_GetParameter($Data, "Admin Email"); //.org
	
	if ($AdminEmail == FALSE)
		$AdminEmail = Whois_GetParameter($Data, "Email"); //.ws
		
	if ($AdminEmail == FALSE)
		$AdminEmail = Whois_GetParameter($Data, "email"); 

	$AdminContact = strpos($Data, "Administrative");

	//echo "Admin Email: ".$AdminEmail."<br>";

	if ($AdminEmail == FALSE && $AdminContact !== FALSE) //.com
		{
		$AtPosition = strpos($Data, "@", $AdminContact);
			
		$StartPosition  = strrpos(substr($Data, 0, $AtPosition), "\n");
		$StartPositionR = strrpos(substr($Data, 0, $AtPosition), "\r");
		$StartPositionS = strrpos(substr($Data, 0, $AtPosition), " ");
		$StartPositionT = strrpos(substr($Data, 0, $AtPosition), "\t");
		$StartPositionC = strrpos(substr($Data, 0, $AtPosition), ":");

		if ($StartPositionR > $StartPosition)
			$StartPosition = $StartPositionR;
		if ($StartPositionS > $StartPosition)
			$StartPosition = $StartPositionS;
		if ($StartPositionT > $StartPosition)
			$StartPosition = $StartPositionT;
		if ($StartPositionC > $StartPosition)
			$StartPosition = $StartPositionC;

		$EndPosition  = strpos($Data, "\n", $AtPosition);
		$EndPositionS = strpos($Data, " ",  $AtPosition);
		$EndPositionR = strpos($Data, "\r", $AtPosition);		

		if ($EndPositionS < $EndPosition && $EndPositionS !== FALSE)
			$EndPosition = $EndPositionS;
		if ($EndPositionR < $EndPosition && $EndPositionR !== FALSE)
			$EndPosition = $EndPositionR;

		$AdminEmail = substr($Data, $StartPosition, $EndPosition - $StartPosition); 

		$AdminEmail = str_replace(")", "", $AdminEmail);
		$AdminEmail = str_replace("(", "", $AdminEmail);
		}

	return $AdminEmail;
}

function Whois_ConvertTime($Time)
{
	$TimeStamp = strtotime($Time);
	$DateFormat = "%m/%d/%Y";

	if ($TimeStamp == -1)
		{
		$TimePosition = strpos($Time, ":") - 2;
		
		$Year = substr($Time, strrpos($Time, " "));
		$Time = substr($Time, 0, strlen($Time) - strlen($Year));
		$Time = substr($Time, 0, $TimePosition)." ".$Year." ".substr($Time, $TimePosition);
		
		//echo "Year: ".$Year."<br>";
		//echo "Time: ".$Time." ".strtotime($Time)."<br><br>";
		}

	return strftime($DateFormat, strtotime($Time));
}

function Whois_GetParameter($Whois, $Parameter)
{
	$ParameterLength = strlen($Parameter) + 1;

	$Start = strpos($Whois, $Parameter.':');
	$StartSP = strpos($Whois, $Parameter);

	if (($Start === FALSE) && ($StartSP !== FALSE))
		$Start = $StartSP;

	if ($Start !== FALSE)
		{
		$Start += $ParameterLength;

		$End = strpos($Whois, "\n", $Start);
		
		if ($End === FALSE)
			$End = strlen($Whois);

		if ($Start == $End)
			$End = strpos($Whois, "\n", $End + 1);

		$Return = trim(substr($Whois, $Start, $End - $Start));
		
		$Return = trim(str_replace(".........", "", $Return));

		//printf("getparam: %s %d %d / %s<br>", $Parameter, $Start, $End, $Return);
		
		return $Return;
		}

	return FALSE;
}

function Page_Count($Entry)
{
	$MySQLHandle = GetDatabaseConnection();

	$query = "SELECT * FROM counter WHERE name='$Entry'";
	$result = mysql_query($query) or die(mysql_error());

	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	$value = 1;

	if ($row == FALSE)
		$query = "INSERT INTO counter (name, value) VALUES ('$Entry', $value)";
	else
		{
		$value = ($row['value'] + 1);
		$query = "UPDATE counter SET value=$value WHERE name='$Entry';";
		}

	$result = mysql_query($query) or die('Query failed: ' . mysql_error());

	mysql_close($MySQLHandle);

	return $value;
}

function Whois_AddToRecentQueries($Domain, $DomainTaken, $PageExists) 
{
	if (substr_count($Domain, ".") != 1)
		return FALSE;

	$MySQLHandle = GetDatabaseConnection();

	$query = "SELECT * FROM queries WHERE domain = '".$Domain."'";
	$result = mysql_query($query) or die(mysql_error());

	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	$DomainExists = (count($row) > 0);

	if ((count($row) == 1) && ($row === FALSE))
			$DomainExists = FALSE;

	if ($DomainExists == TRUE)
		{
		//$query = "DELETE FROM queries WHERE domain = '".$Domain."'";
		$query = "UPDATE queries SET available=".intval($DomainTaken).", pageexists=".intval($PageExists).", lastupdated=".time()." WHERE domain='$Domain'";
		$result = mysql_query($query) or die(mysql_error());
		return;
		}

	//echo "<pre>";
	//print_r($row);
	//echo "</pre>";

	$query = "INSERT INTO queries (domain, available, pageexists, lastupdated) VALUES ('".$Domain."',".intval($DomainTaken).",".intval($PageExists).",".time().");";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());

	mysql_close($MySQLHandle);

	return TRUE;
}

function Whois_DisplayRecentQueries($NumberOfQueries, $Domain='') 
{
	echo "<font style='font-size: 9pt;' face=arial COLOR=#3366CC><b>RECENT REQUESTS</b> (".$NumberOfQueries.")</font>\n";
	echo "<br><br>\n";
	echo "<font size=1 face=verdana COLOR=#3366CC>\n";

	$MySQLHandle = GetDatabaseConnection();

	$query = 'SELECT * FROM queries ORDER BY id DESC';
	$result = mysql_query($query) or die(mysql_error());

	echo "\n<table border=0 cellpadding=0 cellspacing=0 width=100%>\n";
	
	$FirstDomain = $Domain;

	for($i = 0; ($i != $NumberOfQueries) && ($row = mysql_fetch_array($result, MYSQL_ASSOC)); $i++)
		{	
		$Domain = $row['domain'];
		$PageExists = $row['pageexists'];
		$DomainTaken = $row['available'];
		$LastUpdated = strftime("Last Updated: %d %b %Y %I:%M:%S", intval($row['lastupdated']));
		
		if (($i == 0) && ($FirstDomain == ""))
			$FirstDomain = $Domain;

		//Format: 19 Jan 2038 03:14:07
		//echo $LastUpdated."<br>".$row['lastupdated'];

		//echo "Domain: ".$Domain."/".$DomainTaken."/".$PageExists."<br>";

		echo "<tr>";

		if ($DomainTaken == TRUE)
			echo "<td height=19 width=1%><a href='index.php?domain=".$Domain."'><img src=no.gif align=left border=0></a></td>";
		else
			echo "<td height=19 width=1%><a href='index.php?domain=".$Domain."'><img src=ok.gif align=left border=0></a></td>"; 
			
		if (strlen($Domain) > 20)
			echo "<td width=100%><font face=verdana size=1><a href=index.php?domain=".$Domain." title='".$LastUpdated."' style='text-decoration: none; color: black;'>".substr($Domain, 0, 20)."..</a></td>";
		else
			echo "<td width=100%><font face=verdana size=1><a href=index.php?domain=".$Domain." title='".$LastUpdated."' style='text-decoration: none; color: black;'>".$Domain."</td>";

		if ($PageExists == TRUE)
			echo "<td width=19 width=1%><a href='http://".$Domain."/' target='_blank'><img src=page.gif align=left border=0></a></td>";
		else if ($DomainTaken == TRUE)
			echo "<td width=19 width=1%><a href='http://".$Domain."/' target='_blank'><img src=nopage.gif align=left border=0></a></td>";
		else
			echo "<td width=19 width=1%><a href='http://www.kqzyfj.com/click-1711271-10378406' target=_blank><img src=cart5.gif align=left border=0></a><img src='http://www.tqlkg.com/image-1711271-10378406' width=1 height=1 border=0></a></td>";

		echo "</tr>\n";
		}

	mysql_free_result($result);
	mysql_close($MySQLHandle);

	//echo "<tr><td colspan=3 align=right>&nbsp;</td></tr>";
	//echo "<tr><td colspan=3 align=right><font face=verdana size=1><a href='history.php'>More..</a></td></tr>";
	echo "</table>\n";

	echo "<BR>";
	
	echo "<font style='font-size: 9pt;' face=arial COLOR=#3366CC><b>MORE TOOLS</b></font>";
	echo "<font face=verdana size=1><BR><BR>";
	
	echo "<a href='index.php?domain=".$FirstDomain."'>Whois Tool</a><br>";
	echo "<a href='dig.php'>Quick Dig Tool</a><br>";
	echo "<a href='multiwhois.php'>Quick Whois Tool</a><br>";
	echo "<a href='ipwhois.php?arin=".$FirstDomain."'>IPWhois Tool</a><br>";
	echo "<a href='headers.php?domain=".$FirstDomain."'>HTTP Headers Tool</a><br>";
	echo "<a href='trace.php?domain=".$FirstDomain."'>Traceroute Tool</a><br>";
	echo "<a href='ping.php?domain=".$FirstDomain."'>Ping Tool</a><br><br>";
	echo "<font style='font-size: 9pt;' face=arial COLOR=#3366CC><b>CONTACT ME</b></font>";
	echo "<font face=verdana size=1 color=black><BR><BR>";
	echo "Something wrong with the site?<br><a href='mailto:nmoinvaziri@yahoo.com'>Email me</a> or <a href='http://www.nathanm.com/'>Visit My Personal Website</a>";
	
	echo "<br><br>";

	echo "</font><br>";	
}

function File_Read($URL)
{
	$FileHandle = fopen($URL, "r");
	$Data = "" ;
	if ($FileHandle == FALSE)
		{
		echo "Cannot open ".$URL." for read access. ".$FileHandle."<BR>"; 	
		return FALSE;
		}
	while (!feof($FileHandle))
		$Data .= fread($FileHandle, 8192);
	fclose($FileHandle);
	return $Data;
}

function File_Write($URL, $Data, $Access = 'w+')
{
	$FileHandle = fopen($URL, $Access);
	if ($FileHandle == FALSE)
		{
		echo "Cannot open ".$URL." for write access. ".$FileHandle."<BR>"; 	
		return FALSE;
		}
	//echo " ".$Access." <br>";
 	fwrite($FileHandle, $Data);
	fclose($FileHandle);
	return $Data;
}

function HTTP_GetHeaders($URL)
{
	//$URL = str_replace("http://", "", $URL);

	if (substr_count(strtolower($URL), "http://") == 0)
		$URL = "http://".$URL;

	$DomainInfo = @parse_url($URL);
	
	//print_r($DomainInfo);

	$Path = (isset($DomainInfo['path'])) ? $DomainInfo['path'] : '/';

	if ($Path == "") $Path = "/";

	$Headers  = "GET ".$Path." HTTP/1.1\r\n";
	$Headers .= "Accept: */*\r\n";
	$Headers .= "Accept-Language: en-us\r\n";
	$Headers .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n";
	$Headers .= "Host: ".$DomainInfo['host']."\r\n";
	$Headers .= "Connection: close\r\n\r\n";
 
	$SocketHandle = @fsockopen($DomainInfo['host'], 80, $ErrorNumber, $ErrorMessage, 2);

	if ($SocketHandle == FALSE)
		return FALSE;

	fputs($SocketHandle, $Headers);
	
	$ResponseHeaders = "";
	while (feof($SocketHandle) == FALSE)
		{
		$ResponseHeaders .= fgets($SocketHandle, 4096);

 		if (strstr($ResponseHeaders, "\r\n\r\n"))
			break;
		}

    	return $ResponseHeaders;
}

function HTTP_GetPage($URL)
{
	//$URL = str_replace("http://", "", $URL);

	$DomainInfo = @parse_url($URL);

	if (isset($DomainInfo['path']))
		$Path = $DomainInfo['path'];
	else
		$Path = "/";

	if ($Path == "") 
		$Path = "/";

	if (isset($DomainInfo['host']))
		$Host = $DomainInfo['host'];

	if (isset($DomainInfo['path']) && ($DomainInfo['path'] == $URL))
		{
		$Host = $URL;
		$Path = "/";
		}

	if (substr_count(strtolower($URL), "http://") == 0)
		$URL = "http://".$URL;
	
	//echo "<pre>";
	//echo "Path: ".$Path."\r\n";
	//echo "Host: ".$Host."\r\n";
	//print_r($DomainInfo);
	//echo "</pre>";
	
	$Headers  = "GET ".$Path." HTTP/1.1\r\n";
	$Headers .= "Accept: */*\r\n";
	$Headers .= "Accept-Language: en-us\r\n";
	$Headers .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n";
	$Headers .= "Host: ".$Host."\r\n";
	$Headers .= "Connection: close\r\n\r\n";
 	
	$SocketHandle = @fsockopen($Host, 80, $ErrorNumber, $ErrorMessage, 2);

	if ($SocketHandle == FALSE)
		return FALSE;

	fputs($SocketHandle, $Headers);
	
	$ResponseHeaders = "";
	while (feof($SocketHandle) == FALSE)
		{
		$ResponseHeaders .= fgets($SocketHandle, 4096);

 		if (strstr($ResponseHeaders, "\r\n\r\n"))
			break;
		}

	$ResponsePage = "";
	while (feof($SocketHandle) == FALSE)
		{
		$ResponsePage .= fgets($SocketHandle, 4096);
		}

    	return $ResponsePage;
}

function HTTP_GetHeaderParameter($Header, $Parameter)
{
    $ParameterStart = strpos($Header, $Parameter);

    if ($ParameterStart !== FALSE)
        {
        $ParameterString = substr($Header, $ParameterStart);
        $ParameterEnd = strpos($ParameterString, "\r\n") - strlen($Parameter);

        return substr($ParameterString, strlen($Parameter), $ParameterEnd);
        }

    return FALSE;
}

function Directory_ReadContents($Path)
{
	$Contents = array();
	$Handle = opendir($Path);

	while ($File = readdir($Handle)) 
		{            
		if ($File != "." && $File != "..")                
			$Contents[] = $Path.$File;
		}
   
	return $Contents;
}

function Text_WordWrap($Text, $Break, $Cut = "\r\n", $Length = '82', $Shorten = '')
{
	$ReturnText = "";
	$Wrap = $Text;

	//echo "<xmp>".$Wrap."</xmp>";

	while (strlen($Wrap) > $Length)
		{

		$PartWrap = substr($Wrap, 0, $Length);

		if ($Shorten != "")
			{
			$ReturnText .= substr($PartWrap, 0, $Length - strlen($Shorten));
			$ReturnText .= $Shorten;
			return $ReturnText;
			}

		if ($Break != "")
			$LastBreak = strrpos($PartWrap, $Break);
		else
			$LastBreak = $Length;

		if ($LastBreak !== FALSE)
			{
			$LastBreak += strlen($Break);
			$PartWrap = substr($Wrap, 0, $LastBreak);
			}
		else
			$LastBreak = $Length;
		
		$ReturnText .= $PartWrap.$Cut;
	
		$Wrap = substr($Wrap, $LastBreak);

		//echo "<xmp>";
		//echo $PartWrap;
		//echo "Break: ".$Break."LastBreak: ".$LastBreak;
		//echo "</xmp><br>";
		//echo "<xmp>".$Wrap."</xmp><br>";


		}

	$ReturnText .= $Wrap;
	return $ReturnText;
}

function HTML_DecodeSpecialChars($String) 
{
	$TranslationTbl = get_html_translation_table (HTML_ENTITIES);
	$TranslationTbl = array_flip ($TranslationTbl);
	$String   = strtr($String, $TranslationTbl);
	
	$String      = str_replace("#BR#", "", $String);

	return($String);
}

function HTML_Parse($Content)
{
	$Tags = NULL;

	$NumberOfTags = 0;
	$Position = 0;
	$FindNextStop = 0;

	while ($Position <= strlen($Content))
	{
		//echo "Position: ".$Position."<br>";
		//echo "HtmlContent Length: ".strlen($Content)."<br>";
		//echo "<xmp>".substr($Content, $Position)."</xmp>";

		$FindNextTag = strpos($Content, "<", $Position);
	
		if (($FindNextTag === FALSE) || ($Position != $FindNextTag))
			{
			$Tags[$NumberOfTags]['Name'] = "text";

			if ($FindNextTag == FALSE)
				$Tags[$NumberOfTags]['Value'] = substr($Content, $Position, strlen($Content) - $Position);			
			else
				$Tags[$NumberOfTags]['Value'] = substr($Content, $Position, $FindNextTag - $Position);			
			
			$Tags[$NumberOfTags]['CloseTag'] = FALSE;
			$Tags[$NumberOfTags]['Attributes'] = NULL;

			$Position += strlen($Tags[$NumberOfTags]['Value']);

			//echo "Position: ".$Position."<br>";
			//echo "Value Length: ".$Tags[$NumberOfTags]['Name']." ".$Tags[$NumberOfTags]['Value']." ".strlen($Tags[$NumberOfTags]['Value'])."<br>";
			//echo "HtmlContent Length: ".strlen($Content)."<br><br>";

			$NumberOfTags++;
			}
		
	
		if (($FindNextTag === FALSE) || ($Position == strlen($Content)))
			{
			//echo "End of Content.<br>";
			break;
			}	

		$FindNextTag++;

		//echo "FindNextTag: ".$FindNextTag."<br>";

		$FindNextSpace = strpos($Content, " ", $FindNextTag);
		$FindNextBrace = strpos($Content, ">", $FindNextTag);

		$FindNextStop = $FindNextSpace;

		if (($FindNextSpace === FALSE) || ($FindNextBrace < $FindNextSpace))
			$FindNextStop = $FindNextBrace;

		$Tags[$NumberOfTags]['Name'] = substr($Content, $FindNextTag, $FindNextStop - $FindNextTag);
		$Tags[$NumberOfTags]['Value'] = "";
		$Tags[$NumberOfTags]['Attributes'] = "";

		//print_r($Tags[$NumberOfTags]);

		$EndOfTag = FALSE;
		$NumberOfAttributes = 0;

		if ($Tags[$NumberOfTags]['Name'][0] == "!" && $Tags[$NumberOfTags]['Name'][1] == "-")
			{
			$FindNextStop = strpos($Content, "-->", $FindNextTag);
				
			$Tags[$NumberOfTags]['Name'] = "comments";
			$Tags[$NumberOfTags]['Value'] = substr($Content, $FindNextTag - 1, $FindNextStop - $FindNextTag + 4);
			
			$FindNextStop += 3;
			$EndOfTag = TRUE;

			//echo "<xmp>".$Tags[$NumberOfTags]['Value']."</xmp>";
			}

		if ($Tags[$NumberOfTags]['Name'] == "script")
			{
			$FindNextStop = strpos($Content, "</script>", $FindNextTag + 1);

			$Tags[$NumberOfTags]['Value'] = "<xmp>".substr($Content, $FindNextBrace + 1, $FindNextStop - $FindNextBrace - 1)."</xmp>";

			$FindNextStop += 9;
			$EndOfTag = TRUE;
			
			//echo "<xmp>".$Tags[$NumberOfTags]['Value']."</xmp>";
			}

		if ($Tags[$NumberOfTags]['Name'] == "style")
			{
			$FindNextStop = strpos($Content, "</style>", $FindNextTag + 1);

			$Tags[$NumberOfTags]['Value'] = substr($Content, $FindNextBrace + 1, $FindNextStop - $FindNextBrace - 1);

			$FindNextStop += 8;
			$EndOfTag = TRUE;
			
			//echo "<xmp>".$Tags[$NumberOfTags]['Value']."</xmp>";
			}

		while ($EndOfTag === FALSE)
		{
			$FindNextBrace = strpos($Content, ">", $FindNextStop);
			$FindNextEqual = strpos($Content, "=", $FindNextStop);

			//echo "FindNextBrace: ".$FindNextBrace."<br>";
			//echo "FindNextEqual: ".$FindNextEqual."<br>";

			if (($FindNextBrace === FALSE) || ($FindNextBrace != FALSE && $FindNextEqual == FALSE) || ($FindNextBrace < $FindNextEqual))
				{
				$EndOfTag = TRUE;

				//echo "-FindNextStop: ".$FindNextStop."<br>";
				//echo "-FindNextBrace: ".$FindNextBrace."<br>";

				if ($FindNextStop != $FindNextBrace)
					{
					$Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Name'] = substr($Content, $FindNextStop, $FindNextBrace - $FindNextStop);
					$Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Value'] = "";
				
					$FindNextStop += strlen($Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Name']);

					$NumberOfAttributes++;				
					}

				$FindNextStop++;

				//echo "EndOfTag occured.<br>";
				break;
				}

			$FindNextStop = $FindNextEqual; 

			$FindPrevSpace = strrpos(substr($Content, 0, $FindNextStop), " ") + 1;
			
			$Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Name'] = substr($Content, $FindPrevSpace, $FindNextStop - $FindPrevSpace); 

			$FindNextMark  = strpos($Content, "'", $FindNextStop);
			$FindNextQuote = strpos($Content, "\"", $FindNextStop);
			$FindNextSpace = strpos($Content, " ", $FindNextStop);
			$FindNextBrace = strpos($Content, ">", $FindNextStop);

			//echo "FindNextMark: ".$FindNextMark."<br>";
			//echo "FindNextQuote: ".$FindNextQuote."<br>";
			//echo "FindNextSpace: ".$FindNextSpace."<br>";
			//echo "FindNextBrace: ".$FindNextBrace."<br><br>";

			$FindNextStop = $FindNextSpace;

			if (($FindNextQuote != FALSE) && ($FindNextSpace === FALSE || $FindNextQuote < $FindNextSpace) && ($FindNextMark === FALSE || $FindNextQuote < $FindNextMark) && ($FindNextBrace === FALSE || $FindNextQuote < $FindNextBrace))	
				$FindNextStop = strpos($Content, "\"", $FindNextQuote + 1) + 1;

			if (($FindNextMark != FALSE) && ($FindNextSpace === FALSE || $FindNextMark < $FindNextSpace) && ($FindNextQuote === FALSE || $FindNextMark < $FindNextQuote) && ($FindNextBrace === FALSE || $FindNextMark < $FindNextBrace))					
				$FindNextStop = strpos($Content, "'", $FindNextMark + 1) + 1;

			if (($FindNextBrace != FALSE) && ($FindNextSpace === FALSE || $FindNextBrace < $FindNextSpace) && ($FindNextQuote === FALSE || $FindNextBrace < $FindNextQuote) && ($FindNextMark === FALSE || $FindNextBrace < $FindNextMark))					
				$FindNextStop = $FindNextBrace;

			//echo "FindNextBrace: ".$FindNextBrace."<br>";
			//echo "FindNextStop: ".$FindNextStop."<br><br>";	

			$FindNextEqual++;

			$Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Value'] = substr($Content, $FindNextEqual, $FindNextStop - $FindNextEqual);

			//echo $Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Name']."<br>";	
			//echo $Tags[$NumberOfTags]['Attributes'][$NumberOfAttributes]['Value']."<br>";	

			$NumberOfAttributes++;

			//echo $Contents[$FindNextStop + 1]."<br>";		
		}

		//echo "FindNextStop: ".$FindNextStop."<br><br>";	
		
		$Position = $FindNextStop;
		$NumberOfTags++;
	}		

	//echo "<pre>";
	//print_r($Tags);
	//echo "</pre>";

	return $Tags;
}

function HTML_BuildURL($FullURL, $PartURL)
{
	$PartURL = str_replace("\"", "", $PartURL);
	$PartURL = str_replace("'", "", $PartURL);
	$PartURL = str_replace("&amp;", "&", $PartURL);

	$FullURL = str_replace("\"", "", $FullURL);
	$FullURL = str_replace("'", "", $FullURL);
	$FullURL = str_replace("&amp;", "&", $FullURL);

	if (strpos($PartURL, "?http://") == 0 && strpos($PartURL, "?http") !== FALSE)
		{
		$PartURL = substr($PartURL, 1);
		if (strpos($PartURL, "?") === FALSE)
			$PartURL = substr($PartURL, 0, strpos($PartURL, "&&"))."?".substr($PartURL, strpos($PartURL, "&&"));
		$PartURL = str_replace(".com?", ".com/?", $PartURL);
		}

	if (strpos($PartURL, "http://") == 0 && strpos($PartURL, "http://") !== FALSE)
		return $PartURL;
	if (strpos($PartURL, "https://") == 0 && strpos($PartURL, "https://") !== FALSE)
		return $PartURL;

	//echo "..".$FullURL."..".$PartURL."..<BR>";
	
	if (substr_count($FullURL, "/") == 2)
		$FullURL = $FullURL."/";
	
	$LastPath = substr($FullURL, 0, strrpos($FullURL, "/") + 1);

	if ($PartURL[0] == '/')
		{
		$LastPath = substr($LastPath, 0, strpos($LastPath, "/", 9));
		//echo "..".$LastPath."..".$PartURL."..<BR>";
		}

	if ($LastPath[strlen($LastPath) - 1] == '/' && $PartURL[0] == '/')
		$PartURL = substr($PartURL, 1);

	//echo "..".$LastPath.$PartURL."..<BR>";

	return $LastPath.$PartURL;
}

function HTML_GetText($Tags, $URL)
{
	$Text = "";

	for($i = 0; $i != count($Tags); $i++)
		{
		if ($Tags[$i]['Name'] == "text")
			$Text .= $Tags[$i]['Value'];
		}

	return $Text;
}

function HTML_GetComments($Tags, $URL)
{
	$Comments = "";

	for($i = 0; $i != count($Tags); $i++)
		{
		if ($Tags[$i]['Name'] == "comments")
			$Comments .= $Tags[$i]['Value'];
		}

	return $Comments;
}

function HTML_GetStyle($Tags, $URL)
{
	$Style = "";

	for($i = 0; $i != count($Tags); $i++)
		{
		if ($Tags[$i]['Name'] == "style")
			$Style .= $Tags[$i]['Value'];
		}

	return $Style;
}

function HTML_GetScripts($Tags, $URL)
{
	$Scripts = "";

	for($i = 0; $i != count($Tags); $i++)
		{
		if ($Tags[$i]['Name'] == "script")
			$Scripts .= $Tags[$i]['Value'];
		}

	return $Scripts;
}

function HTML_GetLinks($Tags, $URL)
{
	$Links = "";
	$NumberOfLinks = 0;

	for($i = 0; $i != count($Tags); $i++)
	for($x = 0; $x != count($Tags[$i]['Attributes']); $x++)
		{
		if (strcasecmp($Tags[$i]['Name'], "a") == 0 && strcasecmp($Tags[$i]['Attributes'][$x]['Name'], "href") == 0)
			{
			$AddLink = TRUE;
			$LinkURL = HTML_BuildURL($URL, $Tags[$i]['Attributes'][$x]['Value']);
			
			for ($p = 0; $p != count($Links); $p++)
				if (isset($Links[$p]) && ($Links[$p] == $LinkURL))
					$AddLink = FALSE;

			if (substr_count(strtolower($LinkURL), "javascript:") != 0)
				$AddLink = FALSE;

			if ($AddLink == TRUE)
				{
				$Links[$NumberOfLinks] = $LinkURL;
				$NumberOfLinks++;
				}
			}
		}

	return $Links;
}

function HTML_GetImages($Tags, $URL, $ImageLinksOnly)
{
	$Images = "";
	$NumberOfImages = 0;

	for($i = 0; $i != count($Tags); $i++)
	for($x = 0; $x != count($Tags[$i]['Attributes']); $x++)
		{
		if (strcasecmp($Tags[$i]['Name'], "img") == 0 && strcasecmp($Tags[$i]['Attributes'][$x]['Name'], "src") == 0)
			{
			$AddImage = TRUE;

			$ImageSource = HTML_BuildURL($URL, $Tags[$i]['Attributes'][$x]['Value']);
			$ImageLink = "";

			if (strcasecmp($Tags[$i - 1]['Name'], "a") == 0 && strcasecmp($Tags[$i - 1]['Attributes'][$x]['Name'], "href") == 0)
				$ImageLink = HTML_BuildURL($URL, $Tags[$i - 1]['Attributes'][$x]['Value']);

			$ImageExtension = substr($ImageSource, strrpos($ImageSource, ".") + 1);

			if ($ImageLink != "")
				$ImageLinkExtension = substr($ImageLink, strrpos($ImageLink, ".") + 1);

			if ($AddImage == TRUE)
				{
				$Images[$NumberOfImages]['Source'] = $ImageSource;
				$Images[$NumberOfImages]['Link'] = "x".$ImageLink;
	
				$NumberOfImages++;
				}
			}
		}

	//echo "NumberOfImages: ".$NumberOfImages."<br>";

	return $Images;
}

function HTML_PrintLinks($Links)
{
	$Text = "\n";
	for($i = 0; $i != count($Links) && isset($Links[$i]); $i++)
		{
		$LinkSource = $Links[$i];
		$ShortLink = Text_WordWrap($LinkSource, "", "<br>", 75, "...");

		$Text .= "Link: <a href='".$LinkSource."'>".$ShortLink."</a><br>\n";
		}
	return $Text;
}

function HTML_PrintImages($Images)
{
	$Text = "\n";
	for($i = 0; $i != count($Images) && isset($Images[$i]); $i++)
		{
		$SourceLink = $Images[$i]['Source'];
		$ShortLink = Text_WordWrap($SourceLink, "", "<br>", 75, "...");

		$Text .= "Image: <a href='".$SourceLink."'>".$ShortLink."</a><br>\n";
		}

	return $Text;
}

function Parse($URL, $Page, $SearchType)
{
	$Page = str_replace("\t", "", $Page);
	$Page = str_replace("\r", "", $Page);
	$Page = str_replace("\n", "", $Page);
	$Page = str_replace("  ", "", $Page);

	$DomainInfo = @parse_url($URL);

	if (isset($DomainInfo['path']))
		$Path = $DomainInfo['path'];
	else
		$Path = "/";

	if ($Path == "") 
		$Path = "/";

	if (isset($DomainInfo['host']))
		$Host = $DomainInfo['host'];

	if (isset($DomainInfo['path']) && ($DomainInfo['path'] == $URL))
		{
		$Host = $URL;
		$Path = "/";
		}

	if (substr_count(strtolower($URL), "http://") == 0)
		$URL = "http://".$URL;

	$Tags = HTML_Parse($Page);

	$Text = HTML_GetText($Tags, $URL);
	$Comments = HTML_GetComments($Tags, $URL);
	$Style = HTML_GetStyle($Tags, $URL);
	$Scripts = HTML_GetScripts($Tags, $URL);
	$Links = HTML_GetLinks($Tags, $URL);
	$Images = HTML_GetImages($Tags, $URL, FALSE);

	if ($SearchType == 1)
		return HTML_PrintImages($Images);
	else if ($SearchType == 2)
		return $Text;
	else if ($SearchType == 3)
		return HTML_PrintLinks($Links);

	return "";
}

?>