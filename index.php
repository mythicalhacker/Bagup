<?php
include('config.php');
 


error_reporting(E_ALL);



function doesExist($finalfilename){

global $conn;
$result=$conn->query('SELECT count(filename) FROM record WHERE filename="'.$finalfilename.'"');
$row= $result->fetch_assoc();
if($row['count(filename)']==0)
return false;
else return true;	
}

function getExtension($file){
	$i;
	$hasext=false;
	for($i=strlen($file);$i>0;$i--){
		if($file[$i]=='.')
			{
				$hasext=true;
				break;
			}
	}
	if($hasext){
	return substr($file, $i);	
	}
	else
		return "";
}

function getFinalFileName()
{
$finalfilename;

// if file name is not provided, plain and simple set final name		
if($_POST["filename"]=="")
	$finalfilename=$_FILES["file"]["name"];
			
//if new filename is provided butif no extension in new file name
else if(getExtension($_POST["filename"])=="")
	$finalfilename=$_POST["filename"].getExtension($_FILES["file"]["name"]);
		            
//new name and extension both are provided
else{	//if extensions differ use the orignal one
if(strcmp(getExtension($_POST["filename"]),getExtension($_FILES["file"]["name"])))
	$finalfilename=$_POST["filename"].getExtension($_FILES["file"]["name"]);
	//if they dont differ then no problem
else 
	$finalfilename=$_POST["filename"];
}

	return $finalfilename;
}

function updatedb(){
		$finalfilename=getFinalFileName();
		global $conn;
		if(empty($_POST['authname']))
			$_POST['authname']='anonymous';
		$statement=$conn->prepare("INSERT INTO record(filename,author,password) VALUES(?,?,?)");
		$statement->bind_param("sss",$finalfilename,$_POST['authname'],$_POST['delpass']);
		$statement->execute();
		return 1;
}


function listAll(){

	global $conn;
	$result=$conn->query("SELECT * FROM record");
	if($result->num_rows==0)
		echo "No files uploaded";
	else{
		echo '
			<table class="table table-striped panel-body">
				<tr>
				 <th class="text-center">
				 <div class="well-sm"><div class=" glyphicon glyphicon-download-alt"></div>  File</div></th>
				 <th class="text-center" colspan="2">
				 <div class="well-sm"><div class=" glyphicon glyphicon-user"></div>  Author Name</div></th>
				</tr>

		';
		while($row=$result->fetch_assoc()){
			echo '
				<tr>
					<td class="text-center">
					<div class="title">
						<a href="iso/'.$row["filename"].'">'.$row["filename"].'</a>
					</div>
					</td>
					<td class="text-center">
					<div class="author">
						'.$row["author"].'
					</div>
					</td>
				</tr>		
					
			
			';
			
			}
		echo '</table>';
	}



}
//errors
if(isset($_POST["submit"])){
	if(empty($_FILES["file"]["name"]))
		echo "No file selected<br>";
	if(empty($_POST["delpass"]))
		echo "Item Removal Password Empty<br>";
	 
	if(!empty($_FILES["file"]["name"]) && !empty($_POST["delpass"])){
		//FILE UPLOAD PART
		
		$finalfilename=getFinalFileName();
		// time to upload
		
		if(doesExist($finalfilename)==true){

			echo "<p class='alert alert-danger text-center'><strong>This filename already exist! Choose a diferent name</strong></p>";
		}
		else{
			chdir('iso');
			$new_file=fopen($finalfilename,"w");
			chdir('..');

			if(move_uploaded_file($_FILES["file"]["tmp_name"],"iso/".$finalfilename)&&updatedb()){
				echo "<p class='alert alert-success text-center'><strong>File Successfully Uploaded</strong></p>";
			}
			else{
				echo "<p class='alert alert-danger text-center'><strong>Some internal problem occured</strong></p>";
			}
		}




	}
}




?>


<!DOCTYPE html>
<html lang="en">
<head>
<style type="text/css">
	
	.formy{
		margin-left: 5%;
		margin-right:5%;   
	}
	@media only screen and (min-width:480px){
	.formy{
		margin-left: 25%;
		margin-right:25%;   
	}
}
	.jumbotron{
		margin-top: -2%;
	}	

</style>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>deBag!</title>
</head>
<body>
<div class="jumbotron">
<h1 class="text-center"><span class="glyphicon glyphicon-cloud-download"></span> deBag!</h1><br>
<form action="index.php" method="post" enctype="multipart/form-data" class="formy" >
<input type="file" name="file" class="form-control">
<input type="text" name="filename" placeholder="Name Your File" class="form-control">
<input type="text" name="authname" placeholder="Owner Name" class="form-control">
<input type="password" name="delpass" placeholder="Choose a delete password" class="form-control">
<input class="btn-lg btn-success form-control" type="submit" name="submit" value="Bag It" >
</form>
</div>

<h2 class="text-center panel-default"><div class="panel-heading"><p class="glyphicon glyphicon-download-alt"></p> Bagged Files</div></h2>

<?php
//<h3 class="text-center panel-default"><div class="panel-heading glyphicon glyphicon-folder-open">Uploaded Files</div></h3>
?>
<?php
listAll();
?>
<div class="well text-right"><h4><small>Product of Archie</small></h6></div>
</body>
</html>