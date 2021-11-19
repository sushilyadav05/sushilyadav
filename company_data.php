<?php
/******
 * Data base connection 
 * servername->localhost
 * @username-> root 
 * @password-> ''
 * @db name-> industry_data
 * */
global $wpdb, $conns;
$dom = new DOMDocument;
libxml_use_internal_errors(true);
$dom->loadHTML('...');
libxml_clear_errors();
function gh_mysql_connection($conn){
	global $wpdb;
	$servername ="localhost";
	$username ="root";
	$password ="";
	$db ="industry_data";

	$conn = mysqli_connect($servername, $username, $password, $db);
//$conn = new mysqli($servername, $username, $password);
	if (!$conn) {
		return 'falsh';
	} else{
		return $conn;
	}
}

/**** Get @file_get_contents throught url @http://www.mycorporateinfo.com/industry/section/C
 ***** @CIN,@Company Name,@Class,@Status
*****/

$htmlContent = file_get_contents("http://www.mycorporateinfo.com/industry/section/C");


if(!empty($htmlContent)	)
{
	
$DOM = new DOMDocument();
$DOM->loadHTML($htmlContent);
$Header = $DOM->getElementsByTagName('th');
$Detail = $DOM->getElementsByTagName('td');

/*** Get header name of the table ****/

foreach($Header as $NodeHeader) 
{
	$aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
}

/***Get row data/detail table without 
*** header name as key
****/

$i = 0;
$j = 0;
foreach($Detail as $sNodeDetail) 
{
	$aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
	$i = $i + 1;
	$j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
}

/*** Get row data/detail table with header name as key and
** outer array index as row number
****/

for($i = 0; $i < count($aDataTableDetailHTML); $i++)
{
	for($j = 0; $j < count($aDataTableHeaderHTML); $j++)
	{
		$aTempData[$i][$aDataTableHeaderHTML[$j]] = $aDataTableDetailHTML[$i][$j];
	}
}
$aDataTableDetailHTML = $aTempData; unset($aTempData);

foreach($aDataTableDetailHTML as $key=>$aDataTableDetailHTML_DATA)
{
	
	$CIN = $aDataTableDetailHTML_DATA['CIN'];
	$Company_Name = $aDataTableDetailHTML_DATA['Company Name'];
	$Class = $aDataTableDetailHTML_DATA['Class'];
	$Status = $aDataTableDetailHTML_DATA['Status'];
	// Gh_insert_industry_in_db($CIN,$Company_Name,$Class,$Status);
}
}
function Gh_insert_industry_in_db($CIN,$Company_Name,$Class,$Status)
{
	    global $wpdb, $conns;
		
		$conn = gh_mysql_connection($conns);

    if ($conn != 'falsh') {
        $table_name = 'industry_list1';
          $check = "SELECT * FROM $table_name WHERE cin = '" . $CIN . "'";
        $rs = mysqli_query($conn, $check);
        $data = mysqli_fetch_array($rs, MYSQLI_NUM);
        if ($data[0] >= 1) {
            $sql_update = "UPDATE $table_name SET cin='" . $CIN . "', company_name='" . $Company_Name . "', Class='" . $Class . "', Status='" . $Status . "'  WHERE cin = '" . $CIN . "'";
            $conn_update = $conn->query($sql_update);
        } else {
           $sql_insert = "INSERT INTO `industry_list1` (`cin`, `company_name`, `Class`, `Status`) VALUES ('".$CIN."', '".$Company_Name."', '".$Class."', '".$Status."')";
			$conn_insert = $conn->query($sql_insert);
        }
    }
}

$link = mysqli_connect("localhost", "root", "", "industry_data");
// Attempt select query execution
$sql = "SELECT * FROM industry_list1";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
       
		 ?>
	<div class="industry1">
	<table>
	<tr>
	<th>CIN</th>
	<th>Company Name</th>
	<th>Class</th>
	<th>Status</th>
	</tr>
	<?php
	   
        while($row = mysqli_fetch_array($result)){
	$name_comp = str_replace(" ","-",$row['company_name']);
	$name_comp1 =  strtolower($name_comp);
	$name="http://www.mycorporateinfo.com/";
				echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['cin'] . "</td>";
                echo "<td><a href='".$name."$name_comp1"."'>" . $row['company_name'] . "</a></td>";
                echo "<td>" . $row['Class'] . "</td>";
				 echo "<td>" . $row['Status'] . "</td>";
            
			echo "</tr>";
        }
        mysqli_free_result($result);
    } else{
        echo "No records matching your query were found.";
    }
	?>
</table>
</div>
<?php
} else{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
// Close connection
mysqli_close($link);
?>