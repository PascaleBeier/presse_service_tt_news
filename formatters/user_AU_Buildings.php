<?php
class user_AU_Buildings {
	// Note: The buildings constants are defined below.

	/*
	 * Converts a textual location to a map. Some heuristics are used, so it is a
	 * 'best effort' guess.
	 * 
	 * Options could be date and time.
	 *  - parking lot entrance
	 *  - parking lot
	 *  - building corners
	 *  - building entrance
	 *  - dates and times
	 *  - remarks
	 *
	 * Returns a string with either a wrapped link or 'something'.
	 */
	public function textLocationToMap ($location, $options = array()) {
		$retVal = $location;
		$specifiers = preg_split('/(-|\.| )/u', $location, null,  PREG_SPLIT_NO_EMPTY);
		if (count($specifiers) >= 1 && $specifiers[0] === 'DI') {
			array_shift($specifiers);
		}
		$buildingNr = isset($specifiers[0]) ? self::itParkNameToBuildingNr($specifiers[0], true) : '';
		if (array_key_exists($buildingNr, self::$buildings['da_DK']['buildings']) || is_numeric($buildingNr)) {
			$url = self::$buildings['da_DK']['url'] . $buildingNr . '.' . self::$buildings['da_DK']['fileExtension'];
			$retVal = '<a href="' . $url . '">' . $location . '</a>';
		}
		return $retVal;
	}

	/*
	 * Returns the name converted to the building number or the name unmodified if it was not convert.
	 */
	private static function itParkNameToBuildingNr ($name, $caseInsensitive = true) {
		$nameLower = mb_strToLower($name);
		foreach (self::$itParkNames as $buildingNr => $itParkName) {
			if ($caseInsensitive) {
				$itParkName = mb_strToLower($itParkName);
			} 
			if ($nameLower === $itParkName) {
				return $buildingNr;
			}
		}
		return $name;
	}
	
	private static $itParkNames = array(
"5342" => "Ada",
"5800" => "Adorno",
"5340" => "Babbage",
"5344" => "Benjamin",
"5343" => "Bush",
"5126" => "Codd-S",
"5128" => "Codd-M",
"5345" => "Dreyer",
"5346" => "Hopper",
"5350" => "Schön",
"5789" => "Shannon",
"5365" => "Stibitz",
"5341" => "Turing",
"5347" => "Wiener",
"5795" => "Zuse"
	);
	
	private static $buildings = array(
		'da_DK' => array('url' => 'http://unikort.au.dk/cocoon/kort/da/byg',
			'fileExtension' => 'gif',
			'buildings' => array(
"1" => 'Universitetsparken',
"5" => 'IT-byen',
"f" => 'INCUBA Science Park',
"4" => 'Moesgård',
"2" => 'Trøjborg',
"2asb" => 'Handelshøjskolen',
"3" => 'Væksthusene',
"341" => 'Idræt',
"1090" => 'bygning 1090',
"1100" => 'bygning 1100',
"1101" => 'bygning 1101',
"1102" => 'bygning 1102',
"1110" => 'bygning 1110',
"1111" => 'bygning 1111',
"1120" => 'bygning 1120',
"1122" => 'bygning 1122',
"1130" => 'bygning 1130',
"1131" => 'bygning 1131',
"1134" => 'bygning 1134',
"1135" => 'bygning 1135',
"1137" => 'bygning 1137',
"1150" => 'bygning 1150',
"1160" => 'bygning 1160',
"1161" => 'bygning 1161',
"1162" => 'bygning 1162',
"1163" => 'bygning 1163',
"1170" => 'bygning 1170',
"1171" => 'bygning 1171',
"1172" => 'bygning 1172',
"1180" => 'bygning 1180',
"1181" => 'bygning 1181',
"1182" => 'bygning 1182',
"1185" => 'bygning 1185',
"1190" => 'bygning 1190',
"1191" => 'bygning 1191',
"1192" => 'bygning 1192',
"1193" => 'bygning 1193',
"1194" => 'bygning 1194',
"1195" => 'bygning 1195',
"1196" => 'bygning 1196',
"1197" => 'bygning 1197',
"1210" => 'bygning 1210',
"1211" => 'bygning 1211',
"1212" => 'bygning 1212',
"1213" => 'bygning 1213',
"1214" => 'bygning 1214',
"1220" => 'bygning 1220',
"1221" => 'bygning 1221',
"1222" => 'bygning 1222',
"1223" => 'bygning 1223',
"1230" => 'bygning 1230',
"1231" => 'bygning 1231',
"1232" => 'bygning 1232',
"1233" => 'bygning 1233',
"1234" => 'bygning 1234',
"1235" => 'bygning 1235',
"1240" => 'bygning 1240',
"1241" => 'bygning 1241',
"1242" => 'bygning 1242',
"1243" => 'bygning 1243',
"1244" => 'bygning 1244',
"1245" => 'bygning 1245',
"1250" => 'bygning 1250',
"1251" => 'bygning 1251',
"1252" => 'bygning 1252',
"1253" => 'bygning 1253',
"1260" => 'bygning 1260',
"1261" => 'bygning 1261',
"1262" => 'bygning 1262',
"1264" => 'bygning 1264',
"1265" => 'bygning 1265',
"1266" => 'bygning 1266',
"1267" => 'bygning 1267',
"1268" => 'bygning 1268',
"1310" => 'bygning 1310',
"1311" => 'bygning 1311',
"1312" => 'bygning 1312',
"1313" => 'bygning 1313',
"1321" => 'bygning 1321',
"1322" => 'bygning 1322',
"1323" => 'bygning 1323',
"1324" => 'bygning 1324',
"1325" => 'bygning 1325',
"1326" => 'bygning 1326',
"1327" => 'bygning 1327',
"1328" => 'bygning 1328',
"1330" => 'bygning 1330',
"1331" => 'bygning 1331',
"1332" => 'bygning 1332',
"1333" => 'bygning 1333',
"1340" => 'bygning 1340',
"1341" => 'bygning 1341',
"1342" => 'bygning 1342',
"1343" => 'bygning 1343',
"1350" => 'bygning 1350',
"1351" => 'bygning 1351',
"1360" => 'bygning 1360',
"1410" => 'bygning 1410',
"1411" => 'bygning 1411',
"1412" => 'bygning 1412',
"1413" => 'bygning 1413',
"1414" => 'bygning 1414',
"1415" => 'bygning 1415',
"1420" => 'bygning 1420',
"1421" => 'bygning 1421',
"1422" => 'bygning 1422',
"1423" => 'bygning 1423',
"1430" => 'bygning 1430',
"1431" => 'bygning 1431',
"1440" => 'bygning 1440',
"1441" => 'bygning 1441',
"1442" => 'bygning 1442',
"1443" => 'bygning 1443',
"1444" => 'bygning 1444',
"1445" => 'bygning 1445',
"1447" => 'bygning 1447',
"1448" => 'bygning 1448',
"1451" => 'bygning 1451',
"1452" => 'bygning 1452',
"1453" => 'bygning 1453',
"1455" => 'bygning 1455',
"1461" => 'bygning 1461',
"1462" => 'bygning 1462',
"1463" => 'bygning 1463',
"1465" => 'bygning 1465',
"1466" => 'bygning 1466',
"1467" => 'bygning 1467',
"1481" => 'bygning 1481',
"1482" => 'bygning 1482',
"1483" => 'bygning 1483',
"1491" => 'bygning 1491',
"1493" => 'bygning 1493',
"1495" => 'bygning 1495',
"1510" => 'bygning 1510',
"1511" => 'bygning 1511',
"1512" => 'bygning 1512',
"1513" => 'bygning 1513',
"1514" => 'bygning 1514',
"1515" => 'bygning 1515',
"1516" => 'bygning 1516',
"1520" => 'bygning 1520',
"1521" => 'bygning 1521',
"1522" => 'bygning 1522',
"1523" => 'bygning 1523',
"1525" => 'bygning 1525',
"1530" => 'bygning 1530',
"1531" => 'bygning 1531',
"1532" => 'bygning 1532',
"1533" => 'bygning 1533',
"1534" => 'bygning 1534',
"1535" => 'bygning 1535',
"1536" => 'bygning 1536',
"1540" => 'bygning 1540',
"1541" => 'bygning 1541',
"1550" => 'bygning 1550',
"1580" => 'bygning 1580',
"1581" => 'bygning 1581',
"1582" => 'bygning 1582',
"1583" => 'bygning 1583',
"1584" => 'bygning 1584',
"1585" => 'bygning 1585',
"1586" => 'bygning 1586',
"1610" => 'bygning 1610',
"1611" => 'bygning 1611',
"1612" => 'bygning 1612',
"1613" => 'bygning 1613',
"1614" => 'bygning 1614',
"1630" => 'bygning 1630',
"1633" => 'bygning 1633',
"1670" => 'bygning 1670',
"1671" => 'bygning 1671',
"1672" => 'bygning 1672',
"1673" => 'bygning 1673',
"1674" => 'bygning 1674',
"1675" => 'bygning 1675',
"2110" => 'bygning 2110',
"3017" => 'bygning 3017',
"3110" => 'bygning 3110',
"3120" => 'bygning 3120',
"5008" => 'bygning 5008',
"5104" => 'bygning 5104',
"5106" => 'bygning 5106',
"5107" => 'bygning 5107',
"5108" => 'bygning 5108',
"5109" => 'bygning 5109',
"5112" => 'bygning 5112',
"5114" => 'bygning 5114',
"5126" => 'bygning 5126',
"5128" => 'bygning 5128',
"5177" => 'bygning 5177',
"5247" => 'bygning 5247',
"5340" => 'bygning 5340',
"5341" => 'bygning 5341',
"5342" => 'bygning 5342',
"5343" => 'bygning 5343',
"5344" => 'bygning 5344',
"5345" => 'bygning 5345',
"5346" => 'bygning 5346',
"5347" => 'bygning 5347',
"5350" => 'bygning 5350',
"5365" => 'bygning 5365',
"5789" => 'bygning 5789',
"5795" => 'bygning 5795',
"589c" => 'bygning 589c',
"fb01" => 'bygning fb01',
"fb02" => 'bygning fb02',
"fb03" => 'bygning fb03',
"fb04" => 'bygning fb04',
"fb05" => 'bygning fb05',
"fb06" => 'bygning fb06',
"fp01" => 'bygning fp01',
"fp02" => 'bygning fp02',
"fp03" => 'bygning fp03',
"k01e" => 'bygning k01e',
"k03a" => 'bygning k03a',
"k06d" => 'bygning k06d',
"k07b" => 'bygning k07b',
"k09a" => 'bygning k09a',
"k10d" => 'bygning k10d',
"k18b" => 'bygning k18b'
		)
	)
);
}

