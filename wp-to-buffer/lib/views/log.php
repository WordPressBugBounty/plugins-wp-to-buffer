<?php
/**
 * Outputs the Logs WP_List_Table.
 *
 * @package WP_To_Social_Pro
 * @author  WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Logs', 'wp-to-buffer' ); ?>
		</span>
	</h1>
</header>

<hr class="wp-header-end" />

<div class="wrap">
	<?php
	// Search Subtitle.
	if ( $table->is_search() ) {
		?>
		<span class="subtitle left"><?php esc_html_e( 'Search results for', 'wp-to-buffer' ); ?> &#8220;<?php echo esc_html( $table->get_search() ); ?>&#8221;</span>
		<?php
	}
	?>

	<form action="admin.php?page=<?php echo esc_attr( $this->base->plugin->name ); ?>-log" method="post" id="posts-filter">
		<?php
		// Output Search Box.
		$table->search_box( __( 'Search', 'wp-to-buffer' ), 'wp-to-social-log' );

		// Output Table.
		$table->display();
		?>
	</form>
</div><!-- /.wrap -->
