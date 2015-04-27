<?php
include("Yelp-1.php");

class R_DETAIL
{
	private $R_ID;
	private $Address;
	private $review_num;
	private $Star;
	private $Dish_List; //List of Dishes for a given R_ID
	private $Neg_List; //key: Dish_ID, Value: count of reviews
	private $Pos_List;
	private $Output; 
	private $conn;

	function R_DETAIL($R_ID, $conn)
	{
		$this->R_ID = $R_ID;
		$this->conn = $conn;
		$this->Dish_List = array();
	    $this->Neg_List = array();
	    $this->Pos_List = array();
	    $this->Output = array();
	}
	public function getMenu() //SELECT DISTINCT `Dish_Name` FROM `menu` WHERE `R_ID` LIKE '%R_ID'
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$R_ID = $this->R_ID;
		$sql = "SELECT DISTINCT Dish_Name FROM menu WHERE R_ID LIKE '$R_ID';";
		$result = mysqli_query($this->conn, $sql);
		$rowcount = mysqli_num_rows($result);
		$this->Dish_List = array(); //clear the Dish_List array 
		if ($result && $rowcount>0) {
			for($i=0; $i<$rowcount ; $i++) 
			{
				$row = mysqli_fetch_array($result);
				{
					array_push($this->Dish_List, $row['Dish_Name']);
					//echo $i.': ' .$row['Dish_Name']."\n";
				}		
			}
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
	}
	public function putDetail($dish) //tako-wasabi //SELECT `Dates`, `Star` FROM `reviews` WHERE `Star` > 3 AND `Dish_Name` LIKE 'tako-wasabi' AND `R_ID` LIKE 'ippudo-ny-new-york'
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$R_ID = $this->R_ID;
		$sql = "SELECT `Dates`, `Star` FROM `reviews` WHERE `Dish_Name` = '$dish' AND `R_ID` = '$R_ID' AND `Star` > 3;";
		$sql2 = "SELECT `Dates`, `Star` FROM `reviews` WHERE `Dish_Name` = '$dish' AND `R_ID` = '$R_ID' AND `Star` < 4;";
		$pos = mysqli_query($this->conn, $sql);
		$neg = mysqli_query($this->conn, $sql2);
		$pos_count = mysqli_num_rows($pos);
		$neg_count = mysqli_num_rows($neg);
		if ($pos && $neg) {	
			$this->Pos_List['$dish'] = $pos_count;
			$this->Neg_List['$dish'] = $neg_count;
			$this->Output[] = array('R_ID' => $dish, 'Pos' => $pos_count, 'Neg' => $neg_count);
			//echo "Dish: $dish: "."Pos: ".$this->Pos_List['$dish']." Neg: ".$this->Neg_List['$dish']."\n";
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
		//echo "After: ".$this->Pos_List['$dish'];
	}
	public function display() //Call  putDetail();
	{
		for($i=0 ; $i < 5 ; $i++ ) // sizeof($this->Dish_List)
		{
			//echo $this->Dish_List[$i]."\n";
			$this->putDetail($this->Dish_List[$i]);
		}
		function cmp($item1,$item2)
		{
			if($item1['Pos'] == $item2['Pos']) return 0;
			    return ($item1['Pos'] < $item2['Pos']) ? 1 : -1;
		}
		usort($this->Output, 'cmp');
		//print_r($this->Output);
		echo json_encode($this->Output, JSON_NUMERIC_CHECK);
	}
	public function main()
	{
		$this->getMenu();
		$this->display();
	}
}

$feed = new MYSQL(); 
//$R_ID = 'ippudo-ny-new-york';  //$R_ID = '15-east-new-york';
//$dish = 'tako-wasabi'; //$dish = 'sushi-omakase';
$d = new R_DETAIL($_GET['R_ID'], $feed->conn); //$R_ID = $_GET['R_ID']; //$_GET['R_ID']
$d->main();
$feed->closeSQL();

?>