<?php
ob_start();
define("ROOT_PATH","");
define("INCLUDES_PATH", ROOT_PATH."includes/");
define("PATH_TO_CLASS", ROOT_PATH."class/");

require_once(ROOT_PATH."utils.php");
include_once(PATH_TO_CLASS."class.getetutor.php");
include_once(PATH_TO_CLASS."class.studymates.php");
include_once(PATH_TO_CLASS."class.course.php");
include_once(PATH_TO_CLASS."class.departments.php");
include_once(PATH_TO_CLASS."class.utility.php");
require_once(INCLUDES_PATH."chksession.php");

$utility 	= new utility();
$studymates 	= new studymates();
$departments 	= new departments();
$course 		= new course();

if($_SESSION['username']=='') {
	header('Location: index.php');
	exit();
}

$mode	= $utility->cleanData($_POST['mode']);
$id 	= $utility->cleanData($_POST['id']);

if($mode=='view') 
{
	//echo $id; die;
	disphtml("showData(".$id.");");
	
}else if($utility->cleanData($_POST['mode'])=="file_upload") 
{
	//$upload_file_path = "C:/xampp/htdocs/getetutor/uploaded_files";
	
	$filename 		= $_FILES['user_file']['name']; 
    $ext 			= substr($filename, strpos($filename,'.'), strlen($filename)-1); 
	$type 			= substr($_FILES['user_file']['type'],0,strrpos($_FILES['user_file']['type'],"/"));
	

	if (($ext == ".pdf") || ($ext == ".PDF") || ($ext == ".doc") || ($ext == ".DOC") || ($ext == ".docx") || ($ext == ".DOCX") || ($ext == ".xls") || ($ext == ".XLS") || ($ext == ".xlsx") || ($ext == ".XLSX") || ($ext == ".jpg") || ($ext == ".JPG") || ($ext == ".jpeg") || ($ext == ".JPEG") || ($ext == ".gif") || ($ext == ".GIF") || ($ext == ".wmv") || ($ext == ".WMV") || ($ext == ".flv") || ($ext == ".FLV"))
	{
		
		
				  if ($_FILES["user_file"]["error"] > 0)
					{
						$mssg = "Return Code: " . $_FILES["user_file"]["error"] . "<br />";
					}
				  else
					{
									
						if (file_exists("uploaded_files/" . $_FILES["user_file"]["name"])) {
							$mssg = $_FILES["user_file"]["name"] . " already exists. ";
						  }
						else
						  {
								$file_name = time()."_".$_FILES["user_file"]["name"];
								$file_type = $_FILES["user_file"]["type"];
								$file_size = $_FILES["user_file"]["size"];
								
								$max_upload_file = 30*1024*1024;

								if($file_size > $max_upload_file)
								{
									$GLOBALS['admin_msg'] = "File size exceeds. Please try small file [30MB]";
									$_POST['mode']=='';
								}else {
								
									if(!get_magic_quotes_gpc()){
										$file_name = addslashes($file_name);
									}
									
									$filename = dirname(__FILE__).'/uploaded_files/'.$file_name; //die;
									move_uploaded_file($_FILES["user_file"]["tmp_name"], $filename);
									
									
									$studymates->mate_name 		= $file_name;
									$studymates->mate_type 		= $file_type;
									$studymates->mate_size 		= $file_size;
									$studymates->department_id 	= $_POST['department_id'];
									$studymates->course_id 		= $_POST['course_id'];
									$studymates->uploaded_by 	= $_SESSION['id'];
									
									
									if($studymates->save($id="NULL")) 
									{
										$GLOBALS['admin_msg'] = "File Uploaded successfully";
										disphtml("main();");
									} 
									else 
									{
										$GLOBALS['admin_msg'] = $studymates->errors;
										$id=-1;
										disphtml("saveData($id);");
									}
								}
								
								//fclose($fp);
				  
							}
					 }
			
			
		//////////////////////////////////////////////////////////////////////////////////////////////////
	}
	else
	{
		$GLOBALS['admin_msg'] = "File extension is not accepted/ Ivalid File.";
		$_POST['mode']=='';
		disphtml("main();");
	}

} 
else if($mode=='add' || $mode=='edit') 
{
	if($id) 
	{} 
	else 
	{
		$id = -1;
	}
	
	disphtml("saveData(".$id.");");

}else if($mode =='change_status') 
{					  
  	$studymates->activeDeactive($id);
  	$GLOBALS['admin_msg'] = $studymates->errors;
	disphtml("main();");
}else if($mode=='delete' && isset($id)) 
{
	$studymates->deleteData($id);
	$GLOBALS['admin_msg'] = "Data deleted successfully";
	disphtml("main();");
} 
else 
{
	disphtml("main();");
}
	
ob_end_flush();

function main() 
{
	$utility 		= new utility();
	$studymates		= new studymates();
	$departments 	= new departments();
	$course 		= new course();
	
	$user_type = $_SESSION['type'];
	
	$hold_page 		= $utility->cleanData($_POST['hold_page']);
	$orderType 		= $utility->cleanData($_POST['orderType']);
	$fieldName		= $utility->cleanData($_POST['fieldName']);
	$search_mode	= $utility->cleanData($_POST['search_mode']);
	$search_type 	= $utility->cleanData($_POST['search_type']);
	$txt_search		= $utility->cleanData($_POST['txt_search']);
	$txt_alpha		= $utility->cleanData($_POST['txt_alpha']);
	
	if($hold_page>0) {
		$GLOBALS['start'] = $hold_page;
	}
	
	if($mode == "refresh") {
		$member_row = $_POST;
	}
	
	if($orderType && $fieldName) {
		$orderType 	= $orderType=='ASC'?' ASC ':' DESC ';
		$orderBy	= 'ORDER BY '.$fieldName.$orderType;
	} else {
		$orderBy='ORDER BY id DESC';
	}
	
	if($_SESSION['type']=='SUP'){
		$member_sql = " SELECT 	* 
						FROM 	".$studymates->table_name."  ".$orderBy."  
						LIMIT 	".$GLOBALS['start'].",".$GLOBALS['show'];
						
						//echo $member_sql; die;
						
		$row=$studymates->search(" SELECT COUNT(*) FROM ".$studymates->table_name."  ");
		$count=$row[0][0];
	}else {
		$member_sql = " SELECT 	* 
						FROM 	".$studymates->table_name."  WHERE is_active='Y' ".$orderBy."  
						LIMIT 	".$GLOBALS['start'].",".$GLOBALS['show'];
						
						//echo $member_sql; die;
						
		$row=$studymates->search(" SELECT COUNT(*) FROM ".$studymates->table_name."    WHERE is_active='Y'");
		$count=$row[0][0];
	}
	
	$result=$studymates->search($member_sql);
	
	 
?>

<script language="JavaScript">
function show_all()
{
	document.frmSearch.search_mode.value = "";	
	document.frmSearch.txt_search.value  = "";
	document.frmSearch.search_type.value="";
	document.frmSearch.submit();	
}


function search_text()
{
	if(document.frmSearch.search_type.value=="") {
		alert("Please Select A Search Type");
		document.frmSearch.search_type.focus();
		return false;
	}
	if(document.frmSearch.txt_search.value.search(/\S/)==-1)
	{
		alert("Please Enter Search Criteria");
		document.frmSearch.txt_search.focus();
		return false;
	}
	document.frmSearch.search_mode.value = "SEARCH";
	document.frmSearch.submit();
}

function OrderBy(order_type,field_name)
{
	document.frm_opts.fieldName.value=field_name;
	document.frm_opts.orderType.value=order_type;
	document.frm_opts.submit();
}

function ChangeStatus(ID,record_no)
{
	document.frm_opts.mode.value='change_status';
	document.frm_opts.id.value=ID;
	document.frm_opts.hold_page.value = record_no*1;
	document.frm_opts.submit();
}

function viewData(id)
{
	document.frm_opts.mode.value='view';
	document.frm_opts.id.value=id;
	document.frm_opts.submit();
}


function deleteData(id)
{
	var UserResp = window.confirm("Are you sure to remove this?");
	if( UserResp == true ) {
		document.frm_opts.mode.value='delete';
		document.frm_opts.id.value=id;
		document.frm_opts.submit();
	}
}



function showCourse(str, crs)
{
if (str=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","ajaxphp/getcourse.php?q="+str+"&s="+crs,true);
xmlhttp.send();
}
</script>


<form name="file_upload_form" id="file_upload_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="mode" value="file_upload">

	<table width="80%" align="center" border="0" class="border" cellpadding="5" cellspacing="1">
		<tr class="TDHEAD">
			<td colspan="3">File Upload</td>
		</tr>
		
         <?php if($GLOBALS['admin_msg']!='') { ?>
		<tr>
			<td class="ERR" colspan="3" align="left"><?php echo $GLOBALS['admin_msg'];?></td>
		</tr>
        <?php } $GLOBALS['admin_msg']='';?>
          		
        
        <tr>
            <td colspan="2" align="center" valign="top" class="tbllogin"> 
          	<div id="showInfo" align="right"></div></td>
            <td align="right"><b><font color="#FF0000">All * marked 
              fields are mandatory</font></b></td>
          </tr>
        
        <tr>
			<td align="right" valign="top" class="tbllogin" width="30%"><font color="#FF0000">*</font>Select Department</td>
			<td align="center" valign="top" class="tbllogin" width="5%">:</td>
			<td  align="left" valign="top">
			
            	<?php
						$query_dept = "select * from departments where `status`='Y'";
						$row_dept = mysql_query($query_dept);
				?>
				  <select name="department_id" onChange="showCourse(this.value, '')"> 
					<option value="">---Select----</option>	
						<?php while($res_dept = mysql_fetch_array($row_dept)) {?>
						<option value="<?php echo $res_dept['id'];?>"><?php echo $res_dept['name'];?></option>
						<?php }?>
				  </select>
            </td>
		</tr>
        <tr>
			<td align="right" valign="top" class="tbllogin" width="30%">Select Course</td>
			<td align="center" valign="top" class="tbllogin" width="5%">:</td>
			<td  align="left" valign="top" id="txtHint">
            <select name="course_id">
				<option value="">---Select----</option>	
			</select></td>
		</tr>

		<tr>
			<td align="right" valign="top" class="tbllogin" width="30%"><font color="#FF0000">*</font>Select the File Upload</td>
			<td align="center" valign="top" class="tbllogin" width="5%">:</td>
		  <td  align="left" valign="top"><input type="file" name="user_file" id="user_file" class="inplogin" />&nbsp;<span style="color:#FF0000">[Maximum file size must be 30MB]</span></td>
		</tr>		

  <tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><input type="submit" value="Upload" class="button"></td>
</tr>

	</table>

</form>

<table width="98%" align="center" border="0" cellpadding="5" cellspacing="1">
	<tr> 
		<td width="20%" align="center" class="ErrorText">Total records:<? echo $count;?></td>
		<td width="27%" align="center" class="ErrorText">&nbsp;</td>
		<td width="4%" align="center" class="ErrorText"><? //echo $utility->ComboResultPerPage(10,20,30,'frm_opts');?></td>
		<td width="41%" align="center" class="ErrorText"><?php echo $GLOBALS['admin_msg'];?></td>
		<td width="5%" align="right"><a href="javascript:document.frm_opts.submit();" title=" Refresh the page"><img border="0" src="images/icon_reload.gif"></a></td>
        <td width="5%" align="right"></td>
	</tr>
</table>
	
<table width="98%" align="center" border="0" cellpadding="5" cellspacing="2"  class="border">
  <tr class="TDHEAD"> 
    <td colspan="10">Study Materials</td>
  </tr>
  <tr class="text_normal" bgcolor="#E7E7F7"> 
    <td width="10%" align="center" ><div align="center"><strong>SL</strong></div></td>
    <td width="22%" ><strong>Study Material Id</strong></td>
	<td width="30%" ><strong>Material Name</strong></td>
    <td width="33%" ><strong>Material type</strong></td>
    <td width="33%" ><strong>Department Name</strong></td>
    <td width="33%" ><strong>Course Name</strong></td>
    <td width="33%" ><strong>Uploaded By</strong></td>
    <?php if($_SESSION['type']=='SUP') {?>
    <td width="10%" ><div align="center"><strong>Is Active</strong></div></td>
    <?php }?>
    <td width="5%"  ><div align="center"><strong>View</strong></div></td>
     <?php if($_SESSION['type']=='SUP') {?>
    <td width="5%"  ><div align="center"><strong>Delete</strong></div></td>
    <?php }?>
  </tr>
  <?php
	$i=0;
	$cnt=$GLOBALS[start]+1;
	while($result[$i]!=NULL) {
		//$studymat_arr = $studymates->getDepartmentName($result[$i]['code']);
	?>
  <tr onMouseOver="this.bgColor='<?php echo SCROLL_COLOR;?>'" onMouseOut="this.bgColor=''"> 
    <td valign="top" align="center"><?php echo $cnt++;?> </td>
    <td valign="top" align="left"><?php echo $result[$i]['id'];?></td>
    <td valign="top" align="left"><?php echo $result[$i]['mate_name'];?> </td>
    <td valign="top" align="left"><?php echo $result[$i]['mate_type'];?> </td>
    <td valign="top" align="left"><?php echo $result[$i]['department_id'];?> </td>
    <td valign="top" align="left"><?php echo $result[$i]['course_id'];?> </td>
    <td valign="top" align="left"><?php echo $result[$i]['uploaded_by'];?> </td>
    <?php if($_SESSION['type']=='SUP') {?>
    <td valign="top" align="center"><a href="javascript:ChangeStatus(<?php echo $result[$i]['id'];?>,<?php echo $GLOBALS['start']; ?>)" title="<?php echo ($result[$i]['is_active']=='Y')?'Turn off':'Turn on'; ?>"> 
      <?php echo $result[$i]['is_active']=='N' ? "<font color=\"#FF0000\"><b>Inactive</b></font>" : "<font color=\"green\"><b>Active</b></font>"; ?> 
      </a></td>
       <?php } ?>
	   <td align="center" valign="top">
	   <?php if($_SESSION['type']!='SUP' && $result[$i]['is_active']=='N') {
	   ?>
        <img src="images/preview_icon.gif" border="0">
        <?php } else {
				$mtype_arr = explode("/", $result[$i]['mate_type']);
					if($mtype_arr[0]=='video' || $mtype_arr[0]=='image') {?> 
				<a href="videoimage.php?vid=<?php echo $result[$i]['id'];?>&ftype=<?php echo $mtype_arr[0];?>" title="View"><img src="images/preview_icon.gif" border="0"></a>
			<?php }else { ?>
    	<a href="uploaded_files/<?php echo $result[$i]['mate_name'];?>" target="_blank" title="View"><img src="images/preview_icon.gif" border="0"></a>
    <?php } 
		}?>
	</td>
	<?php if($_SESSION['type']=='SUP') {?>
    <td align="center" valign="top"><a href="javascript:deleteData( <?php echo $result[$i]['id'];?>);" title="Delete Material"><img name="xx" src="images/delete_icon.gif" border="0"></a></td>
     <?php }?>
  </tr>
  <?php 
	$i++;
	}
	
	if($i == 0) { 
	?>
  <tr> 
    <td align="center" colspan="9">No records found</td>
  </tr>
  <?php } ?>
</table>
	<?php 
	if($count>0 && $count > $GLOBALS['show']) {
	?>
	<table width="90%" align="center" border="0" cellpadding="5" cellspacing="2">
		<tr>
			<td><?php $utility->pagination($count,"frm_opts");?></td>
		</tr>
	</table>
	<?php } ?>
	
	<form name="frm_opts" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" >
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="id">
		<input type="hidden" name="pageNo" 		value="<?php echo $pageNo;    ?>">
		<input type="hidden" name="pagePerNo" 	value="<?php echo $pagePerNo; ?>">
		<input type="hidden" name="url" 		value="add_user.php">
		<input type="hidden" name="search_type" value="<?php echo $search_type;?>">
		<input type="hidden" name="search_mode" value="<?php echo $search_mode;?>">
		<input type="hidden" name="txt_alpha" 	value="<?php echo $txt_alpha;?>">
		<input type="hidden" name="txt_search"  value="<?php echo $txt_search;?>">
		<input type="hidden" name="hold_page" 	value="">
		<input type="hidden" name="fieldName" 	value="<?php echo $fieldName;?>">
		<input type="hidden" name="orderType" 	value="<?php echo $orderType;?>">
	</form>
<table><tr><td height="82px">&nbsp;</td></tr></table>
<?php } ?>

