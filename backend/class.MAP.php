<?php
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/LIB_http.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/LIB_parse.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/jsonpath-0.8.0.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/simplehtmldom_1_5/simple_html_dom.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/Yelp-1.php");
include("Yelp-1.php");
//set_include_path(get_include_path() . PATH_SEPARATOR . '/path/to/google-api-php-client/src');

class Restaurant
{
	public $R_ID;
	private $T_Review;
	private $Star;
	public $Address;
	public $Long;
	public $Lati;
	private $conn;
	private $url;

	function Restaurant($conn1)
	{
		$this->R_ID = array();
		$this->Address = array();
		$this->T_Review = array();
		$this->Star = array();
		$this->Long = array();
		$this->Lati = array();
		$this->conn = $conn1;
	}
	public function clear()
	{
		$this->R_ID = array();
		$this->Address = array();
		$this->T_Review = array();
		$this->Star = array();
		$this->Long = array();
		$this->Lati = array();
	}
	public function getAddress() 
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$sql = "SELECT R_ID, Address FROM Restaurant;";
		$result = mysqli_query($this->conn, $sql);
		$this->clear();
		$rowcount = mysqli_num_rows($result);
		if ($result && $rowcount>0) {
			for($i=0; $i<$rowcount ; $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				{
					array_push($this->R_ID, $row['R_ID']);
					array_push($this->Address, $row['Address']);
					//echo $i.': ' .$row['R_ID'].": ".$row['Address']."\n";
				}		
			}
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
	}

	public function addr_encode($address, $num)
	{
		 $address = str_replace(' ', '+', $address);
		 $json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address="."$address"."&sensor=true_or_false&key=AIzaSyBmOoTlFrtL_9xaUatQmrFu2KT2KMrH_jo", true);
		 $json = json_decode($json);
		 $lat = $json->results[0]->geometry->location->lat;
		 $lng = $json->results[0]->geometry->location->lng;
		 //echo $this->R_ID[$num].": ".$lat.", ".$lng."\n";
		 array_push($this->Lati, $lat);
		 array_push($this->Long, $lng);
		 echo $this->Lati[$num].", ".$this->Long[$num]."\n";
	}
	public function insertLoc($Long, $Lati, $R_ID)
	{
		if (!$this->conn) {
	    	die("Connection failed: " . mysqli_connect_error());
		}
		//$i=0;
		//echo $this->R_ID[$i].": ".$this->Lati[$i].", ".$this->Long[$i]."\n";
		for($i=0 ; $i<sizeof($this->Long) ; $i++)
		{
			$sql = "UPDATE `yelp`.`restaurant` SET `Lng` = '$Long[$i]', `Lat` = '$Lati[$i]' WHERE `restaurant`.`R_ID` = '$R_ID[$i]';";
			if (mysqli_query($this->conn, $sql)) {
		    	echo $this->Lati[$i].", ".$this->Long[$i]." created successfully"."\n";
			} 
			else {
		    	echo "Error: " . $sql . "\n" . mysqli_error($this->conn);
			}
		}
	}
	public function getName()
	{
		$webpage = curlGet('http://www.yelp.com/search?find_loc=New+York%2C+NY&cflt=restaurants#find_desc&start=0', '0');
		$pages = return_between($webpage, '<div class="page-of-pages', '</div>', EXCL);
		//echo $pages."\n";
		echo $pages = return_between($pages, 'of ', ' ', EXCL);
		//echo $pages."\n"; //10 resteraunts per page
		$fp = fopen("restaurants.txt", 'w');
		$count =0;
		for($i=0 ; $i<($pages*10 - 10)+1 ; $i+=10) //Go through all Restaurant List
		{	
			//echo "GOOGOOO";
			$webpages = curlGet('http://www.yelp.com/search?find_loc=New+York%2C+NY&cflt=restaurants&start='."$i", '0');

			$ADDRESS = refine("ADDRESS", $webpages);
			$STAR = refine("STAR", $webpages);
			$RCOUNT = refine("RCOUNT", $webpages);
			$NAME = refine("NAME", $webpages);
			//insertRestrt($conn, $NAME, $STAR, $RCOUNT, $ADDRESS);
			sleep(1);
			
			for($x=0 ; $x<sizeof($NAME) ; $x++)
			{
				$count++;
				fwrite($fp, $NAME[$x]."\n");
				echo $NAME[$x]."\n";
				//fwrite($fp,"TotalRate: ".$TotalRate[$x].', ');
				//echo "TotalRate: ".$TotalRate[$x].', ';
				//fwrite($fp,"TotalRCount: ".$TotalRCount[$x]."\n");	
				//echo "TotalRCount: ".$TotalRCount[$x]."\n";
			}
		}
		fclose($fp);
	}
}

class MAP
{
	private $usr_Lati;
	private $usr_Lng;
	private $R_Lati;
	private $R_Lng;
	private $Distance;
	private $info_List;
	private $conn;

	function MAP($conn, $usr_Lati, $usr_Lng)
	{
		$this->conn = $conn;
		$this->usr_Lati = $usr_Lati;
		$this->usr_Lng = $usr_Lng;
		$this->info_List = array();
		$this->R_Lati = array();
		$this->R_Lng = array();
	}
	public function getRList($query)
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		//$sql = "INSERT INTO menu (Dish_Name, R_ID, Total_R_Counts, Dish_ID) VALUES ('$Dish[$i]', '$R_ID', $RCount[$i], '$DishID[$i]');";
		$sql = "SELECT R_ID, Lng, Lat FROM restaurant WHERE R_ID LIKE '%$query%';"; //'%$query%'
		$result = mysqli_query($this->conn, $sql); //will return a mysqli_result object if (Select, Show...) // Boolean for INSERT
		//$this->info_List = array();
		if ($result) {
			for($i=0; $i<mysqli_num_rows($result); $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				if(is_string($row['R_ID']))
				{
					$this->info_List["$row[R_ID]"] = 0;
					array_push($this->R_Lng, $row['Lng']);
					array_push($this->R_Lati, $row['Lat']);
					//array_push($this->R_List, $row[R_ID]);
					//echo "$row[R_ID], "."Lng: $row[Lng]"."\n";
				}	
				else
				{
					echo "No Result!"."\n";  //No Display????????
				}	
			}
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
		/*foreach($this->info_List as $x => $x_value)  //dispaly info_List
		{
		    echo "Key=" . $x . ", Value=" . $x_value."\n";
		}*/	
	}
	public function getDistance($R_ID, $lat2, $lon2, $unit) 
	{
		$theta = $this->usr_Lng - $lon2;
		$dist = sin(deg2rad($this->usr_Lati)) * sin(deg2rad($lat2)) +  cos(deg2rad($this->usr_Lati)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		if ($unit == "K") {
			$this->info_List["$R_ID"] = ($miles * 1.609344);
			return ($miles * 1.609344);
		}
		else if ($unit == "N") {
		    $this->info_List["$R_ID"] = ($miles * 0.8684);
		    return ($miles * 0.8684);
		} 
		else {
		    $this->info_List["$R_ID"] = $miles;
		    return $miles;
		}
	}
	public function putDistance()
	{
		$i=0;
		$output = array();
		foreach($this->info_List as $R_ID => $value)  //dispaly info_List
		{
			$dis = $this->getDistance($R_ID, $this->R_Lati[$i], $this->R_Lng[$i], "M");
			$i++;
			$output[] = array('R_ID' => $R_ID, 'Distance' => $dis);
		    //echo "$R_ID: ".$this->info_List[$R_ID]." miles\n";
		}
		function cmp($item1,$item2)
		{
			if($item1['Distance'] == $item2['Distance']) return 0;
			    return ($item1['Distance'] > $item2['Distance']) ? 1 : -1;
		}
		usort($output, 'cmp');
		//print_r($output);
		echo json_encode($output, JSON_NUMERIC_CHECK);
	}
	public function sortDistance()
	{
		array_multisort($this->info_List); //sort distance by value
		foreach($this->info_List as $R_ID => $value)  //dispaly info_List
		{
			$this->getDistance($R_ID, $this->R_Lati[$i], $this->R_Lng[$i]);
			$i++;
		    echo "$R_ID: ".$this->info_List[$R_ID]." miles\n";
		}
	}
	public function main($query)
	{
		$this->getRList($query);
		$this->putDistance();
		//$this->getDistance('10-devoe-brooklyn', -70, 40, "M") . " Miles<br>";
	}
}


$feed = new MYSQL();
//$m = new MAP($feed->conn, 40.729762, -73.996865);
$m = new MAP($feed->conn, $_GET['usr_Lati'], $_GET['usr_Lng']); // $_GET['usr_Lati'], $_GET['usr_Lng']);
$m->main($_GET['query']); //$_GET['query']
$feed->closeSQL();

//$m = new MAP($feed->conn, $_GET['usr_Lati'], $_GET['usr_Lng']);
//$m = new MAP($feed->conn, -73.996865, 40.729762);   //-73.996865, 40.729762
//$m->getDistance('10-devoe-brooklyn', -70, 40, "M") . " Miles<br>";
//$name = '10-devoe-brooklyn';
//$feed->getMenu($name);
//$rst = new Restaurant($feed->conn);
//$rst->getAddress();
//$m->getRList($_GET['query']);
//$m->putDistance();
//echo $rst->Address[3]."\n";
//$rst->addr_encode($rst->Address[3]);
/*for($i=0 ; $i<2 ; $i++) //$i<sizeof($rst->R_ID)
{
	$rst->addr_encode($rst->Address[$i], $i);
}
$rst->insertLoc($rst->Long, $rst->Lati, $rst->R_ID);*/

//$feed->closeSQL();

?>
