<?php
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/LIB_http.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/LIB_parse.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/jsonpath-0.8.0.php");
//include("/Users/lapy110/Desktop/Yelp/Crawler/lib/simplehtmldom_1_5/simple_html_dom.php");

include("LIB_http.php");
include("LIB_parse.php");
include("simple_html_dom.php");

function curlGet($url, $MODE) {
	$ch = curl_init(); // Initialising cURL session
	if($MODE == 'Ajax')
	{
		$header[] = 'Accept-Encoding: gzip, deflate, sdch'; 
		$header[] = 'Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.6,en;q=0.4';
		$header[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36';
		$header[] = 'Accept: */*';
		//$header[] = 'Referer: http://www.yelp.com/menu/peter-luger-steak-house-brooklyn/item/sliced-tomato';
		//$header[] = 'Referer: http://www.yelp.com/menu/'.$name.'/item/'.$food;
		$header[] = 'X-Requested-With: XMLHttpRequest';
		$header[] = 'Cookie: yuv=-Kpx5Sqn-fDj4nXmzceBlhdeeOdiU42j0GBc3ogoAF_F5Dcv-nddUWuTSitOZ7IN_NMQALPeh44VQvGm4FB6ywKeml_wEHDf; bse=78398d1863350f8f30960d1169e28ce8; fd=0; hl=en_US; __qca=P0-1656514339-1427872389597; __utmt=1; __utmt_domainTracker=1; __gads=ID=56fb44fa5b1d9afa:T=1427872389:S=ALNI_MYKeiRlSssXq8dc8JRip4TiXm1L6w; recentlocations=San+Francisco%2C+CA%2C+USA%3B%3B; location=%7B%22city%22%3A+%22Detroit%22%2C+%22zip%22%3A+%22%22%2C+%22address1%22%3A+%22%22%2C+%22address2%22%3A+%22%22%2C+%22address3%22%3A+%22%22%2C+%22state%22%3A+%22MI%22%2C+%22country%22%3A+%22US%22%2C+%22unformatted%22%3A+%22Detroit%2C+MI%22%7D; _gat_www=1; qntcst=D; dm=%7B%22rfb%22%3Atrue%2C%22bap%22%3Atrue%7D; __utma=165223479.1061472007.1427872390.1427872390.1427872390.1; __utmb=165223479.8.10.1427872390; __utmc=165223479; __utmz=165223479.1427872390.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utmv=165223479.|4=account%20level=anon=1; _ga=GA1.2.A380EDE9C22DC684; _gat=1';
		$header[] = 'Connection: keep-alive'; 
		$header[] = 'Cache-Control: no-cache';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$results = curl_exec($ch); // Executing cURL session
	curl_close($ch); // Closing cURL session
	return $results; 
}
function returnXPathObject($item) {
	$xmlPageDom = new DomDocument(); // Instantiating a new DomDocument object
	@$xmlPageDom->loadHTML($item); // Loading the HTML from downloaded page
	$xmlPageXPath = new DOMXPath($xmlPageDom); // Instantiating new XPath DOM object
	return $xmlPageXPath; // Returning XPath object
}
function scrapeBetween($item, $start, $end) {
	if (($startPos = stripos($item, $start)) === false) { // If $start string is not found
		return false; // Return false
	} 
	else if (($endPos = stripos($item, $end)) === false) { // If $end string is not found
		return false; // Return false
	} 
	else {
		$substrStart = $startPos + strlen($start); // Assigning start position
		return substr($item, $substrStart, $endPos - $substrStart);
	// Returning string between start and end positions
	}
}
function refine($input, $webpages) 
{
	$index = parse_array($webpages, '<span class="indexed-biz-name"', '<a class="biz-name');
	$name = parse_array($webpages, '<span class="indexed-biz-name">', '</a>
</span>' );
	$addr = parse_array($webpages, '<span class="neighborhood-str-list">', '/address>' );
	$R_counts = parse_array($webpages, '<span class="review-count rating-qualifier">', '</span>');
	$star = parse_array($webpages, '<div class="rating-large">', 'rating">');
	if($input == "NAME")
	{	
		for($i=0; $i<sizeof($name)  ; $i++)
		{
			$index[$i] = return_between($index[$i], '>', '.', EXCL);
			$name2[$i] = return_between($name[$i], '<a class="biz-name" href="', '" data-hovercard-id', EXCL);
		}
		return $name2;
	}
	else if($input == "ADDRESS")
	{
		for($i=0; $i<sizeof($name) ; $i++)
		{
			$addr[$i] = return_between($addr[$i], '<address>', '</address>', EXCL);
			$Address[$i] = trim(str_replace('<br>', ' ', $addr[$i]));
		}
		return $Address;
	}
	else if($input == "STAR")
	{
		for($i=0; $i<sizeof($name)  ; $i++)
		{
			$star[$i] = trim(return_between($star[$i], 'title="', 'star', EXCL));
		}
		return $star;
	}
	else if($input == "RCOUNT")
	{
		for($i=0; $i<sizeof($name)  ; $i++)
		{
			$R_counts[$i] = trim(str_replace('reviews', '', strip_tags($R_counts[$i]) ));
		}
		return $R_counts;
	}
	else 
	{
		echo "ERROR!";
		//return;
	}
}
function insertRestrt($conn, $Input1, $Input2, $Input3, $Input4)
{
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	for($i=0 ; $i<count($Input1) ; $i++)
	{
		$sql = "INSERT INTO restaurant (R_ID, Star, R_Count, Address) VALUES ('$Input1[$i]', $Input2[$i], $Input3[$i], '$Input4[$i]' );";
		echo $Input1[$i]. "created successfully"."\n";
		if (mysqli_query($conn, $sql)) {
	    	echo "New record created successfully"."\n";
		} 
		else {
	    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
}
function insertMenu($conn, $Dish, $RCount, $R_ID, $Dishsize) //given R_ID, 
{
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	for($i=0 ; $i<sizeof($RCount) ; $i++)
	{
		$sql = "INSERT INTO menu (Dish_Name, R_ID, Total_R_Counts) VALUES ('$Dish[$i]', '$R_ID', $RCount[$i]);";
		echo $Dish[$i]. "created successfully"."\n";
		if (mysqli_query($conn, $sql)) {
	    	echo "New record created successfully"."\n";
		} 
		else {
	    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	$sql2 = "INSERT INTO restaurant (Dish_Num) VALUES ($Dishsize) WHERE R_ID ='$R_ID';";
	echo $R_ID. "created successfully"."\n";
	if (mysqli_query($conn, $sql2)) {
	    echo "New record created successfully"."\n";
	} 
	else {
	   echo "Error: " . $sql2 . "<br>" . mysqli_error($conn);
	}

}

function insertReview($conn, $DishName, $Star, $Dates, $Text, $R_ID, $ReviewID, $Location)
{
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	for($i=0 ; $i<count($Star) ; $i++)
	{
		$sql = "INSERT INTO reviews (Dish_Name, Star, Dates, Review_Text, R_ID, ReviewID, Location) 
		VALUES ('$DishName', $Star[$i], '$Dates[$i]', '$Text[$i]', '$R_ID', '$ReviewID[$i]', '$Location[$i]');";
		//echo $DishName[$i]. "created successfully"."\n";
		if (mysqli_query($conn, $sql)) {
	    	echo $R_ID.": ".$DishName.", ".$Location[$i]." Successfully"."\n";
		} 
		else {
	    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

}
//1. Input url for restaurant at New York
function dowloadResteraunt($conn)
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


class Menu
{
	private $url;
	private $html;
	private $text;
	public $Dish;
	public $T_Review;
	private $detail;
	public $R_ID;
	public $key;
	public $Dish_ID;	

	function Menu($R_Name)
	{
		$this->Dish = array();
		$this->Dish_ID = array();
		$this->T_Review = array();
		$this->detail = array();
		$this->key = array();
		for($i=0 ; $i<sizeof($R_Name) ; $i++)
		{
			$this->R_ID[$i] =  $R_Name[$i];
		}
		$this->url = 'http://www.yelp.com/menu/'."$this->R_ID[0]";
		$this->html = file_get_html($this->url);
	}
	public function removeImg()
	{
		$html = file_get_html($this->url);
		foreach($html->find('img') as $e )
		{
			$e->outertext = '';
		}
		echo $html;
	}
	public function findAllLink()
	{
		foreach($this->html->find('a') as $e )
		{
			echo $e->href . '<br>' . "\n";
		}
	}
	private function findinner($elmt, $Tag, $array)
	{
		foreach($elmt->find($Tag) as $elmt2)
		{
			$tmp = strip_tags($elmt2->innertext);
			$rw = strip_tags($elmt2->find('div.menu-item-details-stats')->innertext);
			array_push($this->$array, $tmp);
			echo $tmp. ' '. $rw."\n";
		}
	}
	private function isError()
	{
		$webpage = curlGet($this->url, '0');
		$status = trim(return_between($webpage, '<span class="page-status">' , 'error.</span>', EXCL)); 
		if($status == '404') //Throw "404 error"
		{
			//echo '404 ERROR'."\n";
			return true;
		}
		//$html = file_get_html($this->url);
		//echo $html->find('spna.page-status')->innertext;
	}
	/*public function findTagClass($Tag, $Class, $ctr)
	{
		$this->url = 'http://www.yelp.com/menu/'.$this->R_ID[$ctr];
		$this->html = file_get_html($this->url);
		if($this->isError())
		{
			echo "404 Error!"."\n";
			return 'ERROR';
		}
		$this->Dish = array();
		$this->key = array();
		$this->T_Review = array();
		foreach($this->html->find($Tag.".".$Class) as $e )
		{
			//$this->findinner($e, $innerTag, $array);
			$name = $e->first_child(); //Find Food Name
			$buffer = ($name->find('a'))[0]; 
			if($buffer)
			{
				$input = return_between($name, 'item/', '>', EXCL);
				array_push($this->Dish, $input );
				$ID = '#'.$this->R_ID[$ctr].'/'.$input.'#';
				//echo 'ID: '.$ID."\n";
				array_push($this->key, $ID);
				//echo $input.": ";	
			}
			else
			{
				$input = trim($e->find('h3')[0]->innertext);
				array_push($this->Dish, $input);
				$ID = '#'.$this->R_ID[$ctr].'/'.$input.'#';
				//echo 'ID: '.$ID."\n";
				array_push($this->key, $ID);
				//echo $input.": ";
			}	
			//echo $name.": ";
			$tmp = $e->find('div.menu-item-details-stats'); //Find Reviews
			if($tmp == NULL)
			{
				array_push($this->T_Review, 0);
				//echo '0'."\n";
			}
			else
			{
				$stat = "#".trim(strip_tags($tmp[0]))."\n";
				$stat = trim(return_between($stat, '#' , 'review', EXCL));
				array_push($this->T_Review, $stat);
				//echo $stat."\n";
			}			
		}
	}*/
	public function display($array)
	{
		foreach($this->$array as $e)
		{
			echo $e."\n";
		}
	}
}

function insertMenu2($conn, $Dish, $RCount, $R_ID, $DishID) //given R_ID, 
{
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	for($i=0 ; $i<sizeof($Dish) ; $i++)
	{
		$sql = "INSERT INTO menu (Dish_Name, R_ID, Total_R_Counts, Dish_ID) VALUES ('$Dish[$i]', '$R_ID', $RCount[$i], '$DishID[$i]');";
		//echo $Dish[$i]. "created successfully"."\n";
		if (mysqli_query($conn, $sql)) {
	    	echo $Dish[$i]." record created successfully"."\n";
		} 
		else {
	    echo "Error: " . $sql . "\n" . mysqli_error($conn)."\n";
		}
	}
	/*$sql2 = "INSERT INTO restaurant (Dish_Num) VALUES ($RCount[$i]) WHERE (R_ID = '$R_ID');";
	echo $R_ID. "created successfully"."\n";
	if (mysqli_query($conn, $sql2)) {
	    echo "New record created successfully"."\n";
	} 
	else {
	   echo "Error: " . $sql2 . "<br>" . mysqli_error($conn);
	}*/
}
class MYSQL
{
	/*8private $servername = "140.112.233.123";
	private $username = "root";
	private $password = "lapy110";
	private $dbname = "yelp";*/
	private $servername = "websys3.stern.nyu.edu";
	private $username = "websysS15GB6"; //http://websys3.stern.nyu.edu/websysS15GB/websysS15GB6
	private $password = "websysS15GB6!!";
	private $dbname = "websysS15GB6";  
	public $R_List; //output a list of similar name of input query
	public $conn; 
	public $Dish_List;
	public $Cnt_List;
	public $jsonDish;
	public $jsonCnt;

	function MYSQL()
	{
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
		$this->R_List = array();
		$this->Dish_List = array();
	 	$this->Cnt_List = array();
	}
	public function closeSQL()
	{
		mysqli_close($this->conn);
	}
	public function getRList($query)
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		//$sql = "INSERT INTO menu (Dish_Name, R_ID, Total_R_Counts, Dish_ID) VALUES ('$Dish[$i]', '$R_ID', $RCount[$i], '$DishID[$i]');";
		$sql = "SELECT DISTINCT R_ID FROM menu WHERE R_ID LIKE '%$query%';"; //'%$query%'
		$result = mysqli_query($this->conn, $sql); //will return a mysqli_result object if (Select, Show...) // Boolean for INSERT
		$this->R_List = array();
		if ($result) {
			for($i=0; $i<mysqli_num_rows($result); $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				if(is_string($row[R_ID]))
				{
					array_push($this->R_List, $row[R_ID]);
					echo "$row[R_ID]"."\n";
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
	}
	public function getMenu($R_ID) 
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$sql = "SELECT Total_R_Counts, Dish_Name FROM menu WHERE (R_ID LIKE '%$R_ID%') ORDER BY menu.Total_R_Counts DESC;";
		$result = mysqli_query($this->conn, $sql);
		$this->R_List = array();
		$this->Cnt_List = array();
		$this->Dish_List = array();
		$rowcount = mysqli_num_rows($result);
		if ($result && $rowcount>0) {
			for($i=0; $i<$rowcount ; $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				{
					array_push($this->Cnt_List, $row[Total_R_Counts]);
					array_push($this->Dish_List, $row[Dish_Name]);
					echo $row[Dish_Name].": ".$row[Total_R_Counts]."\n";
				}		
			}
			$this->jsonDish = json_encode($this->Dish_List);
			$this->jsonCnt = json_encode($this->Cnt_List);
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}

	}
	public function getAllMenu()
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$sql = "SELECT R_ID, Total_R_Counts, Dish_Name FROM menu WHERE Total_R_Counts != 0;";
		$result = mysqli_query($this->conn, $sql);
		$this->R_List = array();
		$this->Cnt_List = array();
		$this->Dish_List = array();
		$rowcount = mysqli_num_rows($result);
		if ($result && $rowcount>0) {
			for($i=0; $i<$rowcount ; $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				{
					array_push($this->Cnt_List, $row[Total_R_Counts]);
					array_push($this->Dish_List, $row[Dish_Name]);
					array_push($this->R_List, $row[R_ID]);
					//echo $row[Dish_Name]."\n";
					//echo $row[Dish_Name].": ".$row[Total_R_Counts]."\n";
				}		
			}
			//$this->jsonDish = json_encode($this->Dish_List);
			//$this->jsonCnt = json_encode($this->Cnt_List);
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
	}
}

class File
{
	private $info;
	private $file;
	private $ctr;

	function File($filename)
	{
		$this->file = fopen($filename, "r");
		$this->ctr = 0;
		$this->info = array();
	}
	public function readRst()
	{
		while (($line = fgets($this->file)) !== false) 
		{
		    $this->info[$this->ctr] = str_replace("\n", '', $line);
		    $this->info[$this->ctr] = str_replace("/biz/", '', $this->info[$this->ctr]);
		    //echo $this->info[$this->ctr]."\n";
		    $this->ctr++;
		}
		fclose($this->file);
		return $this->info;
	}
}
class DISH
{
	public $R_ID;
	public $Dish;
	public $R_Num;
	public $Star;
	public $Review;
	public $ReviewID;
	public $Date;
	public $Location;
	public $Price;

	function DISH()
	{
		$this->Dish = array();
		$this->Star = array();
		$this->Review = array();
		$this->Date = array();
		$this->ReviewID = array();
		$this->Location = array();
		$this->Price = array();
		/*for($i=0 ; $i<sizeof($R_Name) ; $i++)
		{
			$this->R_ID[$i] =  $R_Name[$i];
		}*/
	}
	public function getDish($R_ID, $DishName, $Review_num, $DishPage2, $conn, $begin) //Givne a list of DishName & its ReviewNum
	{
		$star = parse_array( $DishPage2, 'div class="rating-very-large', 'rating'); 
		$Date = parse_array( $DishPage2, 'rating-qualifier', 'span');
			//$Highlight = parse_array($DishPage2, '<span class="highlighted">', '</span>');
		$reviews = parse_array($DishPage2, '"review-content\"', 'class="review-footer clearfix');
		$reviewID = parse_array($DishPage2, '<div class="review review--with-sidebar" data-review-id=', ' data-signup-object');
			//echo $reviewID[2];
		if( $begin == 0)
		{
			$this->Star = array();
			$this->Date = array();
			$this->Review = array();
			$this->ReviewID = array();
			$this->Location = array();
			$this->Price = array();
		}
		$q =0;
		$dom = new simple_html_dom(null, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT);
		$page = $dom->load($DishPage2, true, true);
		foreach($page->find('li.user-location') as $e ) //getLocation
		{
			array_push($this->Location, trim(strip_tags($e->innertext)));
			$q++;	
		}
		/*$v =0;
		foreach($page->find('div.menu-item-prices') as $e ) //getLocation
		{
			array_push($this->Price, $e->innertext);
			echo $e->innertext."\n";
			$v++;	
		}*/
		for($i=0 ; $i<sizeof($star) ; $i++) 
		{
			$star[$i] = return_between($star[$i], 'title="', ' star', EXCL);
			array_push($this->Star, $star[$i]);
			//echo "star: ".$star[$i]." ";
			$Date[$i] = trim(return_between($Date[$i], 'qualifier">', '</span', EXCL));
			array_push($this->Date, $Date[$i]);
			//echo "date: ".$Date[$i]." ";
			$reviews[$i] = return_between($reviews[$i], '<p lang="en">', '</p>', EXCL);
			array_push($this->Review, $reviews[$i]);
				//echo $reviews[$i]."\n";
			$reviewID[$i] = return_between($reviewID[$i], 'data-review-id="', '" data-signup-object', EXCL);
			$reviewID[$i] = "#".$reviewID[$i]."#".$DishName;
			array_push($this->ReviewID, $reviewID[$i]);
			//echo $this->Location[$i].", ";
			//echo 'reviewID: '.$reviewID[$i]."\n";
		}	
		//echo $q." ".$i;
	}
	public function getDishInfo($R_ID, $DishName, $conn, $R_num)
	{
		$DishPage = curlGet('http://www.yelp.com/menu/'.$R_ID.'/item/'.$DishName, '0');
		//echo $num = return_between($DishPage, 'item/'.$DishName.'#menu-reviews" class="i-wrap ig-wrap-menus i-grey-review-menus-wrap num-reviews"><i class="i ig-menus i-grey-review-menus"></i>', 'eview', EXCL);
		//$Review_num = trim(return_between($num, '"', 'r', EXCL));
		$Review_num = $R_num;
		$begin = 0;
		if( $Review_num == 0)
		{
			return;
		}
		else if($Review_num < 21)
		{
			//$DishPage = curlGet('http://www.yelp.com/menu/'.$R_ID.'/item/'.$DishName, '0');
			//$DishPage2 = json_decode($DishPage, true);
			return;
			//$this->getDish($R_ID, $DishName, $Review_num, $DishPage, $conn, $begin);
		}
		else
		{
			for($j=0 ; $j<$Review_num ; $j+=20) //loop through all reviews given a dish: 1.Rate 2.Content 3.Date
			{
				$begin = $j;
				$DishPage = curlGet('http://www.yelp.com/menu/'.$R_ID.'/item/'.$DishName.'/paginate?start='.$j, 'Ajax');
				$DishPage2 = json_decode($DishPage, true);
				$DishPage2 = $DishPage2['body'];
				$this->getDish($R_ID, $DishName, $Review_num, $DishPage2, $conn, $begin);
				$time = rand(3, 8);
				sleep($time);
			}
		}
	}
}
?>
<?php
//$feed = new MYSQL();
//$feed->getMenu('shake-shack-brooklyn'); 
//step: 3864 fixed reviewID problem (before: lots of duplicate entry insert error! )
//step:5593, start echo count num, fixed empty array() problem! (before: num>20 appear error)
//step: 5807, start insert Location
//$feed->getAllMenu();
//$dish = new DISH();
//echo sizeof($feed->Dish_List)."\n";
/*for($i=5062 ; $i<5808 ; $i++)
{
	echo "step: ".$i.", ";
	echo "Num: ".$feed->Cnt_List[$i]."\n";
	$dish->getDishInfo($feed->R_List[$i], $feed->Dish_List[$i], $feed->conn, $feed->Cnt_List[$i]);
	//echo $feed->Dish_List[$i]." ".$feed->R_List[$i]." ".$feed->Cnt_List[$i]."\n";
	//echo 'size: '.sizeof($dish->Star)." ".$dish->Date[0]."\n";
	insertReview($feed->conn, $feed->Dish_List[$i], $dish->Star, $dish->Date, $dish->Review, $feed->R_List[$i], $dish->ReviewID, $dish->Location);
	$time = rand(3, 7);
	sleep($time);
}*/
//$dish->getDishInfo('bogota-latin-bistro-brooklyn', 'sancocho', $feed->conn, 9);
//echo 'size: '.sizeof($dish->Star)." ".$dish->Date[0]."\n";
//$feed->closeSQL();
//getMenu($R_ID);
//$name2 = '21-shanghai-house-new-york-4';
//$name2 = 'peter-luger-steak-house-brooklyn';
//getMenu($name2);
//$R_ID = 'peter-luger-steak-house-brooklyn';
//$DishName = 'sliced-tomato';
//$Review_num = 46;
/*$f = new File("list3.txt");
$R_ID = $f->readRst();
echo sizeof($R_ID);
//echo $R_ID[2];
$doc = new Menu($R_ID);
for($i=0; $i<sizeof($R_ID) ; $i++)
{
	$buffer = $doc->findTagClass('div', 'menu-item-details', $i);
	if($buffer == 'ERROR')
	{
		echo "Skip!";
		continue;
	}
	insertMenu2($conn, $doc->Dish, $doc->T_Review, $doc->R_ID[$i], $doc->key);
	$dishObj = new DISH();
	for($k=0 ; $k<sizeof($doc->Dish) ; $k++)
	{
		$dishObj->getDishInfo($doc->R_ID[$i], $doc->Dish[$k], $conn, $doc->T_Review[$k]);
		insertReview($conn, $doc->Dish[$k], $dishObj->Star, $dishObj->Date, $dishObj->Review, $doc->R_ID[$i], $dishObj->ReviewID);
		sleep(3);
	}
	sleep(5);
}
*/


//$doc->findTagClass('div', 'menu-item-details', 'h3', 'Dish');

//getMenu($conn, $RsList);
//getDishInfo($name1, $dish1, $conn, $num);
//getDishInfo('salt-and-fat-sunnyside', 'asian-pear-salad', $conn, $num);


/*for($i=0 ; $i<sizeof($R_IDs) ; $i++)
{
	$temp = getMenu($RsList[$i]);
	echo $i."\n";
	if( "ERROR" == $temp )
		continue;
}*/

/*
if($_GET['what'] == 'good')
{
	$names = array('Sherlock Holmes', 'John Watson',
	'Hercule Poirot', 'Jane Marple');
	echo getHTML($names);
}
else if($_GET['what'] == 'bad')
{
	$names = array('Professor Moriarty', 'Sebastian Moran',
	'Charles Milverton', 'Von Bork', 'Count Sylvius');
	echo getHTML($names);
}
function getHTML($names)
{
$strResult = '<ul>';
for($i=0; $i<count($names); $i++)
{
$strResult.= '<li>'.$names[$i].'</li>';
}
$strResult.= '</ul>';
return $strResult;
}*/
?>