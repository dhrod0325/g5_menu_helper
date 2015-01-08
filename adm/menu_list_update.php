<?php
$sub_menu = "100290";
include_once( './_common.php' );

check_demo();

if ( $is_admin != 'super' ) {
	alert( '최고관리자만 접근 가능합니다.' );
}

check_token();

// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query( $sql );

$group_code   = null;
$primary_code = null;
$count        = count( $_POST['code'] );

function get_menu_file( $name, $idx ) {
	$keys   = array( 'name', 'type', 'tmp_name', 'error', 'size' );
	$result = array();

	foreach ( $keys as $key ) {
		$value          = $_FILES[ $name ][ $key ][ $idx ];
		$result[ $key ] = $value;
	}

	return $result;
}

function upload_menu_file( $file ) {
	$upload_file = G5_UPLOAD_DIR . '/' . iconv( 'utf-8', 'euc-kr', $file['name'] );

	if ( move_uploaded_file( $file['tmp_name'], $upload_file ) ) {
		$upload_url = '/data/menu/' . $file['name'];
	}

	return $upload_url;
}

for ( $i = 0; $i < $count; $i ++ ) {
	$_POST = array_map_deep( 'trim', $_POST );

	$code    = $_POST['code'][ $i ];
	$me_name = $_POST['me_name'][ $i ];
	$me_link = $_POST['me_link'][ $i ];

	$over_image = get_menu_file( 'me_over_image', $i );
	$out_image  = get_menu_file( 'me_out_image', $i );

	$over_image_url = "";
	$out_image_url  = "";

	if ( ! empty( $over_image['tmp_name'] ) ) {
		$over_image_url = upload_menu_file( $over_image );
	}

	if ( ! empty( $out_image['tmp_name'] ) ) {
		$out_image_url = upload_menu_file( $out_image );
	}

	if ( ! $code || ! $me_name || ! $me_link ) {
		continue;
	}

	$sub_code = '';
	if ( $group_code == $code ) {
		$sql = " select MAX(SUBSTRING(me_code,3,2)) as max_me_code
                    from {$g5['menu_table']}
                    where SUBSTRING(me_code,1,2) = '$primary_code' ";
		$row = sql_fetch( $sql );

		$sub_code = base_convert( $row['max_me_code'], 36, 10 );
		$sub_code += 36;
		$sub_code = base_convert( $sub_code, 10, 36 );

		$me_code = $primary_code . $sub_code;
	} else {
		$sql = " select MAX(SUBSTRING(me_code,1,2)) as max_me_code
                    from {$g5['menu_table']}
                    where LENGTH(me_code) = '2' ";
		$row = sql_fetch( $sql );

		$me_code = base_convert( $row['max_me_code'], 36, 10 );
		$me_code += 36;
		$me_code = base_convert( $me_code, 10, 36 );

		$group_code   = $code;
		$primary_code = $me_code;
	}

	// 메뉴 등록
	$sql = " insert into {$g5['menu_table']}
                set me_code         = '$me_code',
                    me_name         = '$me_name',
                    me_link         = '$me_link',
                    me_target       = '{$_POST['me_target'][$i]}',
                    me_order        = '{$_POST['me_order'][$i]}',
                    me_use          = '{$_POST['me_use'][$i]}',
                    me_mobile_use   = '{$_POST['me_mobile_use'][$i]}',
                    me_over_image  = '{$_POST['me_over_image_url'][$i]}',
                    me_out_image='{$_POST['me_out_image_url'][$i]}'";

	sql_query( $sql );

	if ( ! empty( $over_image_url ) ) {
		$sql = "update {$g5['menu_table']} set me_over_image='{$over_image_url}' where me_code={$me_code}";
		sql_query( $sql );
	}

	if ( ! empty( $out_image_url ) ) {
		$sql = "update {$g5['menu_table']} set me_out_image='{$out_image_url}' where me_code={$me_code}";
		sql_query( $sql );
	}
}

goto_url( './menu_list.php' );
?>
