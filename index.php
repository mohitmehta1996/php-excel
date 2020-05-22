<?php
$mysqli = new mysqli("localhost","root","","test");
if ($mysqli->connect_errno) {
  	echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  	exit();
}

if(isset($_GET['type']) && $_GET['type'] == "init"){
	$query = "SELECT * FROM `excel` order by id ASC";
	$result = $mysqli->query($query);
	$rows = [];
	$total_rows = 10;
	while ($r = mysqli_fetch_assoc($result)){
		$total_rows++;
		$rows[] = $r;
	}
	echo json_encode($rows);exit;
}

if(isset($_GET['type']) && $_GET['type'] == "submit"){
	$data = $_POST['data'];
	$cols = ['A', 'B', 'C'];
	foreach ($data as $key => $row) {
		if($row[0] == ""){ // insert
			unset($row[0]);
			$sql = "INSERT INTO `excel` (" . implode(', ', $cols) . ") VALUES ('" . implode("', '", $row) . "')";
		}
		else{ // update
			$sql = "UPDATE `excel` SET ";
			$sql .= $cols[0]." = '".$row[1]."', ";
			$sql .= $cols[1]." = '".$row[2]."', ";
			$sql .= $cols[2]." = '".$row[3]."'";
			$sql .= " WHERE id = $row[0]";
			echo $sql."<br/>";
		}
        $mysqli->query($sql);
	}
	exit;
}
if(isset($_GET['type']) && $_GET['type'] == "remove"){
	$data = $_POST['data'];
	$sql = "DELETE from `excel` where id IN (" . implode(', ', $data) . ")";
	echo $sql;
	$mysqli->query($sql);
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>PHP Excel</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script> 
	<style type="text/css">
		.table td, .table th {
		    border-right: 1px solid #dee2e6;
		}
		.remove{
		}
		.footer {
		   position: fixed;
		   left: 0;
		   bottom: 0;
		   padding: 10px;
		   background-color: #ddd;
		   color: white;
		   text-align: left;
		}
	</style>
</head>
<body>

	<form class="" method="POST" action="index.php">
		<table class="table">
		  <thead>
		    <tr>
		      <th scope="col">#</th>
		      <th scope="col">A</th>
		      <th scope="col">B</th>
		      <th scope="col">C</th>
		    </tr>
		  </thead>
		  	<tbody class="tbody">
		  </tbody>
		</table>

		<div class="footer">
		 	<button type="button" class="btn btn-success submit">SAVE</button>
		 	<button type="button" class="btn btn-primary" onclick="appendBlankRow(1)">+ Add Row</button>
		 	<button type="button" class="btn btn-danger remove">Remove Selected Rows</button>
		</div>
	</form>

<script type="text/javascript">
	var row_num = 1;
	$(document).ready(function(){
		init();
	});

	function init(){
		row_num = 1;
		$(".tbody").html("");
		$.ajax({
          	url:"index.php?type=init",
          	success:function(data){
            	data = JSON.parse(data);
            	$.each(data, function(i, item) {
				    appendRow(item);
				});
				appendBlankRow(5);
          	}
        })
	}

	function appendBlankRow(num){
		for (var i = 1; i <= num; i++) {
			appendRow();
		}
	}

	function appendRow(row = null){
		var html = "";
		html = '<tr>';
		
		if(row == null){
			html += '<td>'+row_num+'<div class="form-check"><input class="form-check-input" type="checkbox" value=""></div><input type="hidden" class="id form-control" value="" /></td>';
			html += '<td><input type="text" class="form-control" /></td>';
			html += '<td><input type="text" class="form-control" /></td>';
			html += '<td><input type="text" class="form-control" /></td>';
		}
		else{
			html += '<td>'+row_num+'<div class="form-check"><input class="form-check-input" type="checkbox" value="'+row.id+'"></div><input type="hidden" class="id form-control" value="'+row.id+'" /></td>';
			html += '<td><input type="text" class="form-control" value="'+row.A+'" /></td>';
			html += '<td><input type="text" class="form-control" value="'+row.B+'" /></td>';
			html += '<td><input type="text" class="form-control" value="'+row.C+'" /></td>';
		}
		html += '</tr>';

		$('.tbody').append(html);
		row_num++;
	}

	$("body").on("click",".submit", function(){
		var data = []; var temp = []; var add = false; 
		$(".tbody").find("tr").each(function(){
			temp = [];
			add = false;
			$(this).find(".form-control").each(function(){
				if($(this).val() != ""){
					add = true;
				}
				temp.push($(this).val());
			});
			if(add == true){
				data.push(temp);
			}
		});

		$.ajax({
          	url:"index.php?type=submit",
          	type:"post",
          	dataType:"JSON",
          	data:{data:data},
          	success:function(data){
            	init();
          	}
        })
        init();
	});

	$("body").on("change", ".form-check-input", function(){
		if($(this).is(":checked")){
			$(this).closest("tr").css("background-color", "#0000ff26");
		}else{
			$(this).closest("tr").css("background-color", "white");
		}
	});

	$("body").on("click",".remove", function(){
		var l = $('input:checkbox:checked').length;
		if(l == 0){
			alert("No rows are selected.");
			return;
		}
		var r = confirm("Are you sure to remove?");
		if (r == true) {
            var remove_rows = [];
            $.each($("input:checkbox:checked"), function(){
            	if($(this).val() == ""){
            		$(this).closest("tr").remove();
            	}else{
            		remove_rows.push($(this).val());	
            	}
            });

            if(remove_rows.length > 0){
            	$.ajax({
		          	url:"index.php?type=remove",
		          	type:"post",
		          	dataType:"JSON",
		          	data:{data:remove_rows},
		          	success:function(data){
		            	console.log(data);
		          	},
		          	error: function(err){
		          		console.log(err);
		          	}
		        });
            }
            init();
		} 
	});	

	$("body").on("change",".tbody tr:last-child input", function(){
		appendBlankRow(1);
	});	
</script>

</body>
</html>
