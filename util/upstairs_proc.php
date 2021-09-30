<?php
include_once('./_common.php');
include_once(G5_THEME_PATH.'/_include/wallet.php');
include_once(G5_PATH.'/util/purchase_proc.php');

// $debug = 1;
$now_datetime = date('Y-m-d H:i:s');
$now_date = date('Y-m-d');

$mb_id = $member['mb_id'];
$mb_no = $member['mb_no'];

// $input_val ='1';
// $output_val ='169.09';
// $coin_val = 'eth';

$coin_val = '원';
$func = $_POST['func'];
$input_val= $_POST['input_val'];
$output_val = $_POST['output_val'];
$pack_name= $_POST['select_pack_name'];
$pack_id = $_POST['select_pack_id'];
$pack_maker = $_POST['select_maker'];
$it_supply_point = $_POST['it_supply_point'];

$val = substr($pack_maker,1,1);

if($debug){
	$mb_id = 'test9';
	$func = 'new';
	$input_val ='330000'; // 결제금액 (부가세포함)
	$output_val ='300000'; // 구매금액 (부가세제외)
	$pack_name = 'Membership-Package';
	$pack_id = 2021091720;
	$it_supply_point = 0;
}

$target = "mb_deposit_calc";

$target_price = 1;
$pv = $it_supply_point;

if($func == "new"){
	$orderid = date("YmdHis",time()).'01';
}else{
	$orderid = $_POST['od_id'];
}

$sql = "insert g5_shop_order set
	od_id				= '".$orderid."'
	, mb_no             = '".$mb_no."'
	, mb_id             = '".$mb_id."'
	, od_cart_price     = ".$input_val."
	, od_cash    		= ".$target_price."
	, od_name           = '{$pack_name}'
	, od_tno            = '{$pack_id}'
	, od_receipt_time   = '".$now_datetime."'
	, od_time           = '".$now_datetime."'
	, od_date           = '".$now_date."'
	, od_settle_case    = '".$coin_val."'
	, od_status         = '패키지구매'
	, upstair    		= ".$output_val."
	, pv				= ".$pv." ";

if($debug){
	$rst = 1;
	echo "구매내역 Invoice 생성<br>";
	echo $sql."<br><br>";
}else{
	$rst = sql_query($sql);
}

if($func == "new"){
	$logic = purchase_package($mb_id,$pack_id);
}

/* else{
	if($val <= 5){
		$logic = rankup($val,$mb_id,$orderid);
	}
} */


if($rst && $logic){

	$update_point = " UPDATE g5_member set $target = ($target - $input_val) ";

	if($member['mb_level'] == 0){
		$update_point .= ", mb_level = 1 " ;
	}
	
	$update_point .= ", mb_rate = ( mb_rate + {$pv}) ";
	$update_point .= ", mb_save_point = ( mb_save_point + {$output_val}) ";
	$update_point .= ", rank_note = '{$pack_name}', sales_day = '{$now_datetime}' ";
	$update_point .= " where mb_id ='".$mb_id."'";

	if($debug){
		echo "회원 금액 반영<br>";
		echo $update_point."<br>";
	}else{
		sql_query($update_point);
		ob_end_clean();
		echo (json_encode(array("result" => "success",  "code" => "0000", "sql" => $save_hist)));
	}
}else{
	ob_end_clean();
	echo (json_encode(array("result" => "failed",  "code" => "0001", "sql" => $save_hist)));
}

?>

<?if($debug){?>
<style>
    .red{color:red;font-size:16px;font-weight:900}
    .blue{color:blue;font-size:16px;font-weight:900}
    .title {font-weight:900}
    code{text-decoration: italic;color:green;display:block}
    .box{background:#f5f5f5;border:1px solid #ddd;padding:20px;}
</style>
<?}?>
