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
	private $conn;

	function R_DETAIL($R_ID, $conn)
	{
		$this->R_ID = $R_ID;
		$this->conn = $conn;
		$this->Dish_List = array();
	    $this->Neg_List = array();
	    $this->Pos_List = array();
	}
	public function getMenu() //SELECT DISTINCT `Dish_Name` FROM `menu` WHERE `R_ID` LIKE '%R_ID'
	{
		if (!$this->conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		$R_ID = $this->R_ID;
		$sql = "SELECT DISTINCT `Dish_Name` FROM `menu` WHERE `R_ID` LIKE '%R_ID';";
		$result = mysqli_query($this->conn, $sql);
		$rowcount = mysqli_num_rows($result);
		if ($result && $rowcount>0) {
			for($i=0; $i<$rowcount ; $i++) //or mysqli_num_rows($result) ($result->num_rows)
			{
				$row = mysqli_fetch_array($result);
				{
					array_push($this->Dish_List, $row['Dish_Name']);
					echo $i.': ' .$row['Dish_Name']."\n";
				}		
			}
		} 
		else {
		    echo "Error: " . $sql . "\n" . mysqli_error($this->conn)."\n";
		}
	}
	public function putDetail()
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
}

$feed = new MYSQL(); 
$d = new R_DETAIL('ippudo-ny-new-york', $feed->conn);
$d->getMenu();


?>