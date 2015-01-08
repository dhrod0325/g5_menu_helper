<?php

abstract class G5MenuCreator {
	public $request_url;

	public $ca_id;
	public $me_code;
	public $co_id;
	public $bo_table;

	function __construct( $args = array() ) {
		$args = array_merge(
			array( 'ca_id' => '', 'me_code' => '', 'co_id' => '', 'bo_table' => '' ), $args
		);

		$this->var_init( $args );
	}

	abstract function prepareContents( $co_id );

	abstract function prepareRequest( $request_path );

	abstract function prepareBBS( $bo_table );

	abstract function prepareCate( $ca_id );

	function setCoID( $co_id ) {
		$this->co_id = $co_id;
	}

	function setMeCode( $me_code ) {
		$this->me_code = $me_code;
	}

	function var_init( $args ) {
		$this->ca_id    = @$args['ca_id'];
		$this->me_code  = @$args['me_code'];
		$this->co_id    = @$args['co_id'];
		$this->me_code  = is_null( $this->me_code ) ? 10 : $this->me_code;
		$this->bo_table = @$args['bo_table'];

		$request_url       = $_SERVER['PHP_SELF'];
		$this->request_url = explode( '/', $request_url );
	}

	function getMenu( $me_code ) {
		global $g5;

		$q  = "select * from {$g5['menu_table']} where me_use=1 and me_code='{$me_code}'";
		$rs = sql_query( $q );

		$parent = sql_fetch_array( $rs );

		$q  = "select * from {$g5['menu_table']} where me_use=1 and substring(me_code,1,2) = '{$me_code}' and me_code!='{$me_code}'";
		$rs = sql_query( $q );

		$sub = array();

		for ( $i = 0; $row = sql_fetch_array( $rs ); $i ++ ) {
			$sub[] = $row;
		}

		return array(
			'parent' => $parent,
			'sub'    => $sub
		);
	}

	function generateOnMenu( $link ) {
		$on_menu = strpos( $link, $this->co_id ) !== false;
		$on_menu = $on_menu ? 'on' : 'off';

		return $on_menu;
	}

	function getSubMenu() {
		if ( ! empty( $this->co_id ) ) {
			$this->prepareContents( $this->co_id );
		} else {
			if ( ! empty( $this->request_url ) ) {
				$this->prepareRequest( $this->request_url );
			}
		}

		if ( ! empty( $this->bo_table ) ) {
			$this->prepareBBS( $this->bo_table );
			$this->setCoID( $this->bo_table );
		}

		if ( ! empty( $this->ca_id ) ) {
			$this->prepareCate( $this->ca_id );
		}

		$sub_menu = $this->getMenu( $this->me_code );

		ob_start();
		?>
		<h3><img src="<?= $sub_menu['parent']['me_out_image'] ?>" alt=""/></h3>
		<ul class="itemClass subMenu">
			<?php
			foreach ( $sub_menu['sub'] as $row ) {
				$on_menu = $this->generateOnMenu( $row['me_link'] );
				?>
				<li>
					<a href="<?= $row['me_link'] ?>" target="_<?= $row['me_target'] ?>" class="<?= $on_menu ?>"
					   data-over="<?php echo $row['me_over_image'] ?>"
					   data-out="<?php echo $row['me_out_image'] ?>">

						<?php if ( $row['me_out_image'] ) { ?>
							<img src="<?= $on_menu == 'on' ? $row['me_over_image'] : $row['me_out_image'] ?>"
							     alt="<?php echo $row['me_name'] ?>"/>
						<?php } else { ?>
							<?php echo $row['me_name'] ?>
						<?php } ?>

					</a>
				</li>
			<?php
			}
			?>
		</ul>
		<?php
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}
}