<?php

ob_start();
session_start();

 $header='SMS';
$username = $_SESSION["regSuccess"];
if(empty($username)){ ?>
	<script>
		window.location.href = 'addstore.php';
	</script>
<?php }
include_once 'header.php';
error_reporting(0);
include_once './includes/config.inc.php';
$db = db_connect();
$sql = mysql_query("SELECT * from StoreUsers where Username ='".$username."'");
$userdetails = mysql_fetch_array($sql);
$userid=$userdetails['UserId'];
$heading='sms';
include_once './includes/config.inc.php';
auth();
	$url='https://rest.nexmo.com/account/get-balance/?api_key=3e1997ac&api_secret=60525d05b4f53e80';
  	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	//execute post
	$result = curl_exec($ch);         
	$info  = curl_getinfo($ch); 
	$error = curl_errno($ch);  
	curl_close($ch);  
	$remaining_array=json_decode($result,true); 
//echo "<pre>";print_r($remaining_array);exit;
$db = db_connect();
 if($_REQUEST['getData'])
{
	$order = $_REQUEST['order'];
	$page = $_REQUEST['page'];
	$limit = $_REQUEST['limit'];
	$start = ($page-1)*$limit;
	
	if("icon-circle-arrow-up"==$order)
		$or = 'ASC';
	else
		$or = 'DESC';
	$column = $_REQUEST['column'];
	if($column == "sms_from")
		$cby = "sms_from";
	 
	else if($column == "sms_to")
		$cby = "sms_to";
	  

//
	  $sql = "SELECT count(1) as 'sms_count', `mesage_unique_id`,message FROM `sms` group by `mesage_unique_id` order by created_date desc
	LIMIT $start , $limit";
	$stores = $db->get_rows($sql);
	#var_dump($sql);die;
	$count_sql = "SELECT count(1) as 'sms_count', `mesage_unique_id`,message FROM `sms` group by `mesage_unique_id` order by created_date desc";
	$count_res = mysql_query($count_sql );
	$rows_total = mysql_num_rows($count_res);
	echo json_encode(array('reviews'=>$stores, "total"=>$rows_total));
	exit;
}
if($_REQUEST['deleteReview'])
{
	$mesage_unique_id = $_REQUEST['mesage_unique_id'];
	echo mysql_query("DELETE FROM `sms` WHERE `mesage_unique_id`='".$mesage_unique_id."'"); 
	echo mysql_affected_rows();
	exit;
}
//$sms_list = $db->get_rows("SELECT sms_id,sms_from,sms_to,created_date,message,server_response,COUNT(sms_id) AS total from sms order by sms_id desc");
$sms_list = $db->get_rows("SELECT count(1) as 'sms_count', `mesage_unique_id`,message FROM `sms` group by `mesage_unique_id` order by created_date desc ");
?>  
  <script>
	 window.addEventListener("orientationchange", function() {
				window.location.hef='';
			});
function getData(page){
	var order = $("#current_order_ele").attr('order');
	var column = $("#current_order_ele").attr('bind');
	
	var limit = 10;
	if(!page)
		page = 1;
	
	$.ajax({
		url: location.href,
		data: { 
			getData:1,
			order:order,
			column: column,
			store: $('#filter_store').val(),
			keyword: $('#keyword_search').val(),
			page:page,
			limit:limit
		},
		error: function(){alert('Errorr')},
		beforeSend: function(){
			$("#loading_img").show()
		},
		complete: function(){
			$("#loading_img").hide()
		},
		datatype:'json',
		success: function(output){
			 
			var str='';
			output = jQuery.parseJSON(output);
			
			var x = 1;
			$("#data-table").find('tbody').empty();
			
				$.each(output.reviews,function(item){
					var data = output.reviews;
					if(x%2 == 0)
						var row_class='even';
					else
						var row_class='odd';
					x++; 
					str="<tr class='"+row_class+"'><td>"+data[item].message+"</td><td><a href='sms_sent_list.php?mesage_unique_id="+data[item].mesage_unique_id+"'>"+data[item].sms_count+"</td><td class='actions text-center'><a mesage_unique_id='"+data[item].mesage_unique_id+"' class='confirm_delete' href='#'><i class='fa fa-trash' id='trash'></i></a></tr>";
					$("#data-table").find('tbody').append(str);
					
				})
				var totalrows = output.total;
				var no_links = Math.ceil(totalrows/limit);
				var act = '';
				$("#paginationDiv").empty();
				for(var i =1;i<=no_links;i++)
				{
					if(page == i)
						act = 'active';
					else
						act = '';
					$("#paginationDiv").append("<li class='paginate_button " + act+"' page='"+i+"'><a href='#'>"+i+"</a></li>");
				}
			
		}
	});
}
$(document).on("click", "#trash", function(){
		if(confirm("Are you sure to delete?"))
		{
			var mesage_unique_id = $(this).parent().attr('mesage_unique_id');
			$.ajax({
			url: location.href,
			data: { 
				deleteReview: 1,
				mesage_unique_id: mesage_unique_id
			},
			beforeSend: function(){},
			complete: function(){},
			success: function(data){
				if(data.trim()=='1')
					alert("Success");
				//getData('icon-circle-arrow-up','cust_cname');
				getData();
			}
			});
		}
		
		return false;
	});	  
$(document).ready(function(){
	getData();
	$('#data-table').find('th').on('click',function(){
		if($(this).find('div').length > 0)
		{
			var order = $(this).find('div').attr('class');
			var column = $(this).find('div').attr('id');
			
			$("#current_order_ele").attr('order',$('#'+column).attr('class'));
			$("#current_order_ele").attr('bind',column);
			getData();
			//getData($('#'+column).attr('class'),column);
			if(order=="icon-circle-arrow-up")
				$('#'+column).attr('class','icon-circle-arrow-down')
			else
				$('#'+column).attr('class','icon-circle-arrow-up')
		
		}
	});
	$("#search_button").click(function(){
		//getData('icon-circle-arrow-up','cust_cname');
		getData();
	});
	
	$(document).on("click", ".paginate_button", function(){
		
		getData($(this).attr('page'));
	});
	$(document).on("touchstart", ".pagination_li", function(){
		
		getData($(this).attr('page'));
	});
 
	 
})
</script>
	
      
      <!-- Left side column. contains the logo and sidebar -->
      <?php 
//$result_remaining=  mysql_fetch_assoc(mysql_query("SELECT * FROM `sms` WHERE `server_response` like '%remaining-balance%' order by sms_id DESC limit 0,1"));

//$remaining_array=json_decode($result_remaining['server_response'],true);
 ?>
        	<div class="row">
            	<?php include ROOT."admin-left.php"; ?>
        <!-- Content Header (Page header) -->
   <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
        <!-- Content Header (Page header) -->
<h2 class="head-text">SMS List</h2>

        <!-- Main content -->
        <section class="content">
          <!-- Small boxes (Stat box) -->
          <div class="row">
			<div class="col-lg-12 col-sm-12 col-xs-12">
            	<div class="box">
                <div class="box-header"> 
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div class="dataTables_wrapper form-inline dt-bootstrap">
                   <div class="row">
                      <div class="col-lg-12 col-sm-12 col-xs-12">
                      <a class="btn btn-success pull-right" href="sendsms.php">Send Sms</a><br/>
                      <h3 class="pull-right">Balance Remaining :<?= round($remaining_array['value']/0.0057);?></h3>
                       
					   </div>
						</div>
                  <div class="row">
                      <div class="col-lg-12 col-sm-12 col-xs-12">
                          <div class="table-responsive"><table id="data-table" class="table table-bordered table-striped">
                            <thead>
                              <tr>
							<th>Message<!--<a><div id='cust_cname' class="icon-circle-arrow-up"></div></a>--></th>
							<th>Sms Count <!--<a><div id='store_cname' class="icon-circle-arrow-up"></div></a>--></th>
							 <th>Action</th>
							 
						</tr>
                            </thead>
							<img id='loading_img' align='center' style='display:none;margin-left:43%' src="../img/ajax-loader.gif">
                            <tbody>
                             
                            </tbody>
                          </table></div>
                          <div class="row">
                             <div class="col-lg-12 col-sm-12 col-xs-12">
                                 <div class="dataTables_paginate paging_simple_numbers">
                                     <div>
                                         <ul class="pagination" id="paginationDiv">
                                         </ul>
                                     </div>
                                 </div>
                             </div>
        					</div>
                      </div>
                  </div></div>
                </div><!-- /.box-body -->
              </div>
            </div>

          </div><!-- /.row -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
       
<input type='hidden' id='current_order_ele' bind='cust_cname' order='icon-circle-arrow-up'>
<!-- <img id='loading_img' align='center' style='display:none; margin-left:43%' src="../img/ajax-loader.gif"> -->
</body>
<?php include ROOT."themes/footer.inc.php"; ?>
</html>