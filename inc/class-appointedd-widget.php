<?php

class Appointedd_Widget extends WP_Widget{
    function __construct() {
		load_plugin_textdomain( 'appointedd' );
		
		parent::__construct(
			'appointedd_widget',
			__( 'Appointedd Widget' , 'appointedd'),
			array( 'description' => __( 'Display the Appointedd filter form' , 'appointedd') )
		);

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'css' ) );
		}
    }
    
    function css(){

    }

    function form( $instance ) {
        $services = Appointedd_Admin::get_selected_mapped_fields();

		if ( $instance && isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		else {
			$title = __( 'Appointedd Filter' , 'appointedd' );
		}
        
        if ( $instance && isset( $instance['ceremony'] ) ) {
			$ceremony = $instance['ceremony'];
		}
		else {
			$ceremony = __( 'Appointedd Filter' , 'appointedd' );
		}
?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'appointedd'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
            <label for="<?php echo $this->get_field_id( 'Ceremony' ); ?>"><?php esc_html_e( 'Ceremony:' , 'appointedd'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'ceremony' ); ?>" name="<?php echo $this->get_field_name( 'ceremony' ); ?>" >
            <option value="all" <?php echo $selected ? "selected" : ""; ?>>All</option>
            <?php

            foreach($services as $service){
				$key = $service->id;
				$display = $service->display;
                if($key == $ceremony){
                    $selected = true;
                }
                ?>
                <option value="<?php echo $key;?>" <?php echo $selected ? "selected" : ""; ?>><?php echo $display;?></option>
            <?php
            }

            ?>    
            </select>
		</p>

<?php
    }
    
    function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['ceremony'] = strip_tags( $new_instance['ceremony'] );
		return $instance;
	}

	function widget( $args, $instance ) {
        /* do_action("appointed_script");        
        do_action("appointed_style");  */ 
        require(APPOINTEDD__PLUGIN_DIR . "inc/helpers/class-appointedd-resource-groups.php");
        require(APPOINTEDD__PLUGIN_DIR . "admin/inc/class-appointedd-admin.php");
        
        $ceremony = $instance['ceremony'];
        $services = Appointedd_Admin::get_selected_mapped_fields();
		$locations = ResourceGroups::get_locations();

        include(APPOINTEDD__PLUGIN_DIR . "views/appointedd-filter-form.php");
        
	}
}

function appointedd_register_widgets() {
	register_widget( 'Appointedd_Widget' );
}

add_action( 'widgets_init', 'appointedd_register_widgets' );

function action_widgets_init()
{
    register_sidebar(
        array(
            'name'          => 'Appointedd',
            'id'            => 'appointedd',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<h5 class="widget-title">',
            'after_title'   => '</h5>',
        )
    );
}
add_action('widgets_init', 'action_widgets_init');

function appointedd_widget() {
    if(!is_admin()) {
        ob_start();
        dynamic_sidebar('appointedd');
        return ob_get_clean();
    }
}

add_shortcode('appointedd_widget', 'appointedd_widget');