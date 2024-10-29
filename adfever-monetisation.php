<?php
/*
Plugin Name: Adfever Monétisation 
Plugin URI: http://www.adfever.com
Description: Adds adFever's advertising functionnalities to wordpress.  
Author: adFever
Version: 1.0.2
Author URI: http://www.adfever.com
*/

if (!function_exists('add_action')) {
    header('HTTP/1.0 404 Not Found');
    header('Location: ../../../404');
    exit;
}
global $wp_version;
// Including once adfever class
require_once ("classes/adfever.class.php") ;

// we add the front-end ajax support  -> http://codex.wordpress.org/AJAX_in_Plugins
add_action('wp_ajax_AFajax', 'AFajaxcallback');
add_action('wp_ajax_nopriv_AFajax', 'AFajaxcallback');

// We get the plugin's option to initialize it
$AF_options = get_option('AF_options');

// admin actions
if ( is_admin() ){ 
  add_action( 'admin_menu', 'AF_add_admin_menu' );
  add_action( 'admin_enqueue_scripts', 'AF_load_admin_scripts');
  add_action( 'admin_init', 'AF_register_settings' );
  add_action('admin_notices', 'AF_admin_notices');
  // displaying admin errors
  function AF_admin_notices(){
     settings_errors();
  } 
} else {
  // non-admin enqueues, actions, and filters
  add_action( 'wp_print_scripts', 'adfever_add_my_stylesheets' );
  add_action( 'wp_head', 'adfever_generate_css');
  if ($AF_options['AF_act_footer'] == 1 && $AF_options['AF_accept_footer'] == 1 && $AF_options['AF_FID'] != '') {
  	add_action( 'wp_head', 'AF_footer');
  }
  if ($AF_options['AF_act_siteunder'] == 1 && $AF_options['AF_SUID'] != '') {
  	add_action( 'wp_head', 'AF_siteunder');
  }
  if ($AF_options['AF_act_slidein'] == 1 && $AF_options['AF_SIID'] != '') {
  	add_action( 'wp_head', 'AF_slidein');
  }
  if ($AF_options['AF_act_lienssponsos'] && $AF_options['AF_AID'] != '') {
  	if ($AF_options['AF_autoshow_afterpost'] == 1 || $AF_options['AF_autoshow_beforepost'] == 1 ) {
  		add_action( 'the_content', 'AF_add_outer');
  	}
  }
  add_action('wp_footer', 'AF_lienstexte');
  
  
}


function AF_footer() {
	$options = get_option('AF_options');
	echo '<!-- encart 100% Footer 2 - 2013-10-31 --> <script type="text/javascript">ad6is("'.$options['AF_FID'].'");</script> <!-- encart 100% Footer 2 -->';
}

function AF_siteunder() {
	$options = get_option('AF_options');
	echo '<!-- encart Site-under 1 - 2013-10-31 --> <script type="text/javascript">ad6is("'.$options['AF_SUID'].'");</script> <!-- encart Site-under 1 -->';
}

function AF_slidein() {
$options = get_option('AF_options');
echo '<!-- encart Slide-In 1 - 2013-10-31 --> <script type="text/javascript">ad6is("'.$options['AF_SIID'].'");</script> <!-- encart Slide-In 1 -->';
}

function AF_lienstexte() {
	$options = get_option('AF_options');
	// ajout liens texte
	if ($options['AF_SID'] != '' && $options['AF_act_lienstexte'] == 1 && $options['AF_LTID'] != '') {
	echo '<script type="text/javascript" src="http://adfever.fr.intellitxt.com/intellitxt/front.asp?ipid='.$options['AF_LTID'].'"></script>';
	}
}


// Adding the admin menu
function AF_add_admin_menu() {
	$adfever_monetisation = add_menu_page( 'AdFever Monétisation', 'AdFever Monétisation', 'manage_options', 'adfever-monetisation', 'AF_options_do_page_general', plugins_url( 'adfever-monetisation/img/icon.png' ), 61 );
	
	$double_menu_bug_fix = add_submenu_page('adfever-monetisation','Options générales AdFever','Options générales','manage_options','adfever-monetisation','AF_options_do_page_general');
	
	$adfever_lienssponsos = add_submenu_page( 'adfever-monetisation', 'Liens sponsorisés AdFever', 'Liens sponsorisés', 'manage_options', 'adfever-liens-sponsorises', 'AF_options_do_page_liens_sponsos');
}

// register options to the wordpress settings API
function AF_register_settings() {
	register_setting('AF_options', 'AF_options', 'AF_options_validate');	
}

// we load the needed libs, scripts for the admin panel
function AF_load_admin_scripts( ) {
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('AF_admin_scripts', plugins_url('js/admin_scripts.js', __FILE__), array('wp-color-picker'), false, true );
}

// Enqueue plugin style and scripts
function adfever_add_my_stylesheets() {
		$AF_options = get_option('AF_options');
		if ($AFoptions['AF_custom_styles'] == 0) {
        wp_register_style( 'adfever_styles', plugins_url('adfever_styles.css', __FILE__) );
        wp_enqueue_style( 'adfever_styles' );
        }
        // AJAX script
        wp_enqueue_script( "AFajaxcallback", plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ) );
        wp_localize_script( 'AFajaxcallback', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'AFajaxscripts-nonce' ), 'AF_options' => $AF_options ) );
        
        // footer script
        if ($AF_options['AF_act_footer'] == 1 || $AF_options['AF_act_siteunder'] == 1 || $AF_options['AF_act_slidein']) {
        	 wp_enqueue_script("AF_footer", "http://c.ad6media.fr/l.js");
        }
}

// Validates the AF_options when passed through the WP seetings API
function AF_options_validate($args){
	
	        $existing = get_option('AF_options'); // this contains get_option( 'Foo' );
	        if ( !is_array( $existing ) || !is_array( $args ) ) // something went wrong
	            return $args;
	        $args = array_merge( $existing, $args );
	    

    //$args will contain the values posted in the settings form
    if(!isset($args['AF_SID']) || !ctype_digit($args['AF_SID'])){
        $args['AF_SID'] = '';
    add_settings_error('AF_options', 'AF_invalid_SID', 'Votre SID n\'est pas valide', $type = 'error');   
    }
	if(!isset($args['AF_AID']) || !ctype_digit($args['AF_AID'])){
	    $args['AF_AID'] = '';
	//add_settings_error('AF_options', 'AF_invalid_AID', 'Votre AID n\'est pas valide', $type = 'error');   
	}
	
    //make sure you return the args
    return $args;
}

// sets the default values if none are set on plugin activation
function AF_set_default_options() {
	$options = get_option('AF_options');
	// if no options set, we set the default value
	if ($options == false) {
		$defaults = array(
			'AF_act_lienssponsos' => 0,
			'AF_act_lienstexte' => 0,
			'AF_act_footer' => 0,
			'AF_accept_footer' => 0,
			'AF_act_slidein' => 0,
			'AF_act_siteunder' => 0,
			'AF_autoshow_afterpost' => 1,
			'AF_autoshow_beforepost' => 0,
			'AF_display_type' => 'line',
			'AF_global_border' => 0,
			'AF_global_border_top' => 1,
			'AF_global_border_left' => 1,
			'AF_global_border_right' => 1,
			'AF_global_border_bottom' => 1,
			'AF_border' => 0,
			'AF_border_top' => 1,
			'AF_border_left' => 1,
			'AF_border_right' => 1,
			'AF_border_bottom' => 1,
			'AF_custom_styles' => 0,
			'AF_title_size' => 13,
			'AF_text_size' => 13,
			'AF_link_size' => 13,
			'AF_disclaimer_size' => 9,
			'AF_disclaimer_position' => 'BR',
			'AF_num_to_display' => 3
		);
		update_option( 'AF_options', $defaults );
	}		
}
register_activation_hook( __FILE__, 'AF_set_default_options' );



//////////////////////////////////////////
// Admin option page
//////////////////////////////////////////

function AF_options_do_page_general() {
	?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"></div><h2>Configuration AdFever Monétisation</h2>
	<p><strong>Liens utiles:</strong> <a href="http://www.adfever.com/?utm_source=adminpluginWP" target="_blank">S'inscrire sur AdFever</a> | <a href="mailto:editeurs@adfever.com">Aide</a> | <a href="mailto:editeurs@adfever.com">Contact</a></p>
	
	<p><strong>Pour utiliser le plugin de monétisation, vous devez avoir un compte AdFever et y avoir ajouté un site.</strong></p>
	
	<form method="post" action="options.php">
	<?php settings_fields( 'AF_options' ); ?>
	    <?php do_settings_sections( __FILE__ );
	    $options = get_option( 'AF_options' );
	     ?>
	 <h3>Identification</h3>    
	<label for="AF_options[AF_SID]">Votre SID AdFever: <span> *</span></label>
	<input type="text" name="AF_options[AF_SID]" value="<? echo (isset($options['AF_SID']) && $options['AF_SID'] != '') ? $options['AF_SID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
	<input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	<p>Votre SID AdFever est disponible dans votre interface AdFever (un SID par site), rubrique Mes sites : <a href="http://www.adfever.com/editor/site/list " target="_blank">http://www.adfever.com/editor/site/list</a>.
	<br />Les formats nécessitent un identifiant unique chacun, contactez-nous pour que nous vous les envoyons : <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>. Réponse humaine ultra rapide!
	</p>
	
	
	<hr />
	<h3>Liens sponsorisés</h3>
	<p>
	Votre AID AdFever vous permet d'activer les liens sponsorisés.
	</p>
	<p>
	<strong>Activer les liens sponsorisés</strong>&nbsp;&nbsp;&nbsp;
	<input type="radio" name="AF_options[AF_act_lienssponsos]" value="1" <?php if ($options['AF_act_lienssponsos'] == 1) echo "checked"?> /> Oui
	<input type="radio" name="AF_options[AF_act_lienssponsos]" value="0" <?php if ($options['AF_act_lienssponsos'] != 1) echo "checked" ?> /> Non
	</p>
	<p>
	<label for="AF_options[AF_AID]">Votre AID : <span> *</span></label>
	<input type="text" name="AF_options[AF_AID]" value="<? echo (isset($options['AF_AID']) && $options['AF_AID'] != '') ? $options['AF_AID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
				    <input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	
	<?php if($options['AF_AID'] == '') { ?>
		Pas d'AID? Demandez en envoyant un mail à <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>.
	<?php } ?>
	</p>
	<p>Rémunération: au clic<br />
	<?php
	if ($options['AF_act_lienssponsos'] == 1 && $options['AF_AID'] != '') { 
	?>
	<a href="<?php echo admin_url('admin.php?page=adfever-liens-sponsorises');?>">Aller à la configuration des liens sponsorisés</a>
	<?php } ?>
	</p>
	
	
	
	<hr />
	<h3>Liens Texte</h3>
	<p>
	<strong>Activer les liens texte</strong>&nbsp;&nbsp;&nbsp;
	<input type="radio" name="AF_options[AF_act_lienstexte]" value="1" <?php if ($options['AF_act_lienstexte'] == 1) echo "checked"?> /> Oui
	<input type="radio" name="AF_options[AF_act_lienstexte]" value="0" <?php if ($options['AF_act_lienstexte'] != 1) echo "checked" ?> /> Non
	</p>
	<p>
	<label for="AF_options[AF_AID]">Votre LTID : <span> *</span></label>
	<input type="text" name="AF_options[AF_LTID]" value="<? echo (isset($options['AF_LTID']) && $options['AF_LTID'] != '') ? $options['AF_LTID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
				    <input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	
	<?php if($options['AF_LTID'] == '') { ?>
		Pas de LTID? Demandez en envoyant un mail à <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>.
	<?php } ?>
	</p>
	<p>Rémunération: au clic<br /></p>




	<hr />
	<h3>Footer</h3>
	<p>
	<strong>Activer le footer</strong>&nbsp;&nbsp;&nbsp;
	<input type="radio" name="AF_options[AF_act_footer]" value="1" <?php if ($options['AF_act_footer'] == 1) echo "checked"?> /> Oui
	<input type="radio" name="AF_options[AF_act_footer]" value="0" <?php if ($options['AF_act_footer'] != 1) echo "checked" ?> /> Non
	</p>
	<p>
	<label for="AF_options[AF_FID]">Votre FID : <span> *</span></label>
	<input type="text" name="AF_options[AF_FID]" value="<? echo (isset($options['AF_FID']) && $options['AF_FID'] != '') ? $options['AF_FID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
				    <input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	
	<?php if($options['AF_FID'] == '') { ?>
		Pas de FID? Demandez en envoyant un mail à <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>.
	<?php } ?>
	</p>
	<p>Rémunération: CPM fixe 1€<br />
	<strong>Attention, le footer AdFever ne doit pas être ajouté en même temps qu’un autre footer.</strong><br />
	<input type="hidden" name="AF_options[AF_accept_footer]" value="0" />
	<input type="checkbox" name="AF_options[AF_accept_footer]" id="AF_options[AF_accept_footer]" value="1" <?php if ($options['AF_accept_footer'] == 1) { echo "checked"; } ?>>  Je suis d’accord 
	</p>	
	
	
	<hr />
	<h3>Site Under</h3>
	<p>
	<strong>Activer le site under</strong>&nbsp;&nbsp;&nbsp;
	<input type="radio" name="AF_options[AF_act_siteunder]" value="1" <?php if ($options['AF_act_siteunder'] == 1) echo "checked"?> /> Oui
	<input type="radio" name="AF_options[AF_act_siteunder]" value="0" <?php if ($options['AF_act_siteunder'] != 1) echo "checked" ?> /> Non
	</p>
	<p>
	<label for="AF_options[AF_SUID]">Votre SUID : <span> *</span></label>
	<input type="text" name="AF_options[AF_SUID]" value="<? echo (isset($options['AF_SUID']) && $options['AF_SUID'] != '') ? $options['AF_SUID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
				    <input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	
	<?php if($options['AF_SUID'] == '') { ?>
		Pas de SUID? Demandez en envoyant un mail à <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>.
	<?php } ?>
	</p>
	<p>Rémunération: CPM fixe 1€<br /></p>
	
	
	
	<hr />
	<h3>Slide In</h3>
	<p>
	<strong>Activer le slide in</strong>&nbsp;&nbsp;&nbsp;
	<input type="radio" name="AF_options[AF_act_slidein]" value="1" <?php if ($options['AF_act_slidein'] == 1) echo "checked"?> /> Oui
	<input type="radio" name="AF_options[AF_act_slidein]" value="0" <?php if ($options['AF_act_slidein'] != 1) echo "checked" ?> /> Non
	</p>
	<p>
	<label for="AF_options[AF_SIID]">Votre SIID : <span> *</span></label>
	<input type="text" name="AF_options[AF_SIID]" value="<? echo (isset($options['AF_SIID']) && $options['AF_SIID'] != '') ? $options['AF_SIID'] : '';?>" size="8" maxlength="10" />&nbsp;&nbsp;
			    <input type="submit" value="Enregistrer" class="button button-primary" name="submit" />
	
	<?php if($options['AF_SIID'] == '') { ?>
		Pas de SIID? Demandez en envoyant un mail à <a href="mailto:editeurs@adfever.com" target="_blank">editeurs@adfever.com</a>.
	<?php } ?>
	</p>
	<p>Rémunération: CPM fixe 1€<br /></p>
	
	<hr />
	<p>
	<strong>Merci d’utiliser le plugin AdFever Monétisation. Vous aimez le plugin ? <a href="http://wordpress.org/support/view/plugin-reviews/adfever-monetisation?filter=5" target="_blank">Ajoutez un commentaire ici</a></strong> 
	</p>
	
	
	<p>
		    <input type="submit" value="Enregistrer les options" class="button button-primary" name="submit" />
	</p>
	</div>
	<?php
	// print_r($options);
}


function AF_options_do_page_liens_sponsos() {
	?>
	<div class="wrap">
	    <div id="icon-options-general" class="icon32"></div><h2>Configuration Liens sponsorisés AdFever</h2>
	    <p><strong>Liens utiles:</strong> <a href="http://www.adfever.com/?utm_source=adminpluginWP" target="_blank">S'inscrire sur AdFever</a> | <a href="mailto:editeurs@adfever.com">Aide</a> | <a href="mailto:editeurs@adfever.com">Contact</a></p>
	    
	    <form method="post" action="options.php"><hr />
	    <?php settings_fields( 'AF_options' ); ?>
	        <?php do_settings_sections( __FILE__ );
	        $options = get_option( 'AF_options' );
	         ?> 
	    
	    
	    <hr /><h3>Affichage des annonces</h3>
	    <p>
	    <strong>
	    Afficher automatiquement les annonces &nbsp;</strong>&nbsp;&nbsp;&nbsp;
	    <input type="hidden" name="AF_options[AF_autoshow_beforepost]" value="0" />
	    
	    <input type="checkbox" name="AF_options[AF_autoshow_beforepost]" id="AF_options[AF_autoshow_beforepost]" value="1" <?php if ($options['AF_autoshow_beforepost'] == 1) { echo "checked"; } ?>>
	    <label for="AF_options[AF_autoshow_beforepost]">Avant l'article</label>&nbsp;&nbsp;&nbsp;
	    <input type="hidden" name="AF_options[AF_autoshow_afterpost]" value="0" />
	    <input type="checkbox" name="AF_options[AF_autoshow_afterpost]" id="AF_options[AF_autoshow_afterpost]" value="1" <?php if ($options['AF_autoshow_afterpost'] == 1) { echo "checked"; } ?>>
	    <label for="AF_options[AF_autoshow_afterpost]">Après l'article</label>
	    </p>
	    
	    <p>
	    Vous pouvez placer les annonces manuellement dans votre thème. Ajoutez le code PHP suivant dans le fichier single.php de votre thème (ou dans un autre fichier de template, à l'intérieur de la "Loop" wordpress).
	    <br />
	    <code>if (function_exists(AF_theme_callback)) {AF_theme_callback(get_the_ID());}</code>
	    	    </p>
	    	    
	    
	    
	    <label for="AF_options[AF_num_to_display]"><strong>Nombre d'annonces à afficher</strong></label>
	    <select name="AF_options[AF_num_to_display]" id="AF_options[AF_num_to_display]">
	    <?php 
	    for ($i=1; $i<11; $i++) {
	    ?>
	    	<option value="<?php echo $i;?>" <?php if ($options['AF_num_to_display'] == $i) echo "selected"; ?>><?php echo $i; ?></option>
	    <?php
	    }
	    ?>
	    </select>
	    </p>
	    
	    <hr />
	    <h3>Présentation des annonces</h3>
	    <?php // TITRE DES ANNONCES ?>
	    		<p>
	    		<strong>Titre des annonces</strong>&nbsp;&nbsp;&nbsp;
	    		
	    		<label for="AF_options[AF_title_size]">Taille</label>&nbsp;&nbsp;&nbsp;
	    		<select name="AF_options[AF_title_size]" id="AF_options[AF_title_size]">
	    		<?php 
	    		for ($i=8; $i<16; $i++) {
	    		?>
	    			<option value="<?php echo $i;?>" <?php if ($options['AF_title_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
	    		<?php
	    		}
	    		?>
	    		</select>
	    		&nbsp;&nbsp;&nbsp;
	    		<label for="AF_options[AF_title_font]">Police</label>&nbsp;&nbsp;&nbsp;
	    				<select name="AF_options[AF_title_font]" id="AF_options[AF_title_font]">
	    					<option value="none" <?php if ($options['AF_title_font'] == "none") echo "selected"; ?>>Garder la police de mon site</option>
	    					
	    					<option value="'Arial', Helvetica, sans-serif" <?php if ($options['AF_title_font'] == "'Arial', Helvetica, sans-serif") echo "selected"; ?>>'Arial', Helvetica, sans-serif</option>
	    					
	    					<option value="'Times New Roman', Times, serif" <?php if ($options['AF_title_font'] == "'Times New Roman', Times, serif") echo "selected"; ?>>'Times New Roman', Times, serif</option>
	    					
	    					<option value="'Courier New', Courier, mono" <?php if ($options['AF_title_font'] == "'Courier New', Courier, mono") echo "selected"; ?>>'Courier New', Courier, mono</option>
	    					
	    				</select>
	    		<p>		
	    		<label for="AF_options[AF_title_color]" style="margin-top: -15px; display: inline-block;">Couleur</label> &nbsp;
	    		<input name="AF_options[AF_title_color]" style="position: relative; top: 9px;" type='text' class='color-field' value="<?php if(isset($options['AF_title_color'])) { echo $options['AF_title_color']; }?> ">
	    		</p>		
	    				
	    				
	    		</p>
	    
	    		<?php // DESCRIPTION DES ANNONCES ?>
	    		<p>
	    		<strong>Texte des annonces</strong>&nbsp;&nbsp;&nbsp;
	    		
	    		<label for="AF_options[AF_text_size]">Taille</label>&nbsp;&nbsp;&nbsp;
	    		<select name="AF_options[AF_text_size]" id="AF_options[AF_text_size]">
	    		<?php 
	    		for ($i=8; $i<16; $i++) {
	    		?>
	    			<option value="<?php echo $i;?>" <?php if ($options['AF_text_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
	    		<?php
	    		}
	    		?>
	    		</select>
	    		&nbsp;&nbsp;&nbsp;
	    		<label for="AF_options[AF_text_font]">Police</label>&nbsp;&nbsp;&nbsp;
	    				<select name="AF_options[AF_text_font]" id="AF_options[AF_text_font]">
	    					<option value="none" <?php if ($options['AF_text_font'] == "none") echo "selected"; ?>>Garder la police de mon site</option>
	    					
	    					<option value="'Arial', Helvetica, sans-serif" <?php if ($options['AF_text_font'] == "'Arial', Helvetica, sans-serif") echo "selected"; ?>>'Arial', Helvetica, sans-serif</option>
	    					
	    					<option value="'Times New Roman', Times, serif" <?php if ($options['AF_text_font'] == "'Times New Roman', Times, serif") echo "selected"; ?>>'Times New Roman', Times, serif</option>
	    					
	    					<option value="'Courier New', Courier, mono" <?php if ($options['AF_text_font'] == "'Courier New', Courier, mono") echo "selected"; ?>>'Courier New', Courier, mono</option>
	    					
	    				</select>
	    				
	    		<p>
	    		<label for="AF_options[AF_text_color]" style="margin-top: -15px; display: inline-block;">Couleur</label> &nbsp;
	    		<input name="AF_options[AF_text_color]" style="position: relative; top: 9px;" type='text' class='color-field' value="<?php if(isset($options['AF_text_color'])) { echo $options['AF_text_color']; }?> ">
	    		</p>		
	    				
	    		</p>
	    		
	    		
	    		<?php // LIENS DES ANNONCES ?>
	    		<p>
	    		<strong>Lien des annonces</strong>&nbsp;&nbsp;&nbsp;
	    		
	    		<label for="AF_options[AF_link_size]">Taille</label>&nbsp;&nbsp;&nbsp;
	    		<select name="AF_options[AF_link_size]" id="AF_options[AF_link_size]">
	    		<?php 
	    		for ($i=8; $i<16; $i++) {
	    		?>
	    			<option value="<?php echo $i;?>" <?php if ($options['AF_link_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
	    		<?php
	    		}
	    		?>
	    		</select>
	    		&nbsp;&nbsp;&nbsp;
	    		<label for="AF_options[AF_link_font]">Police</label>&nbsp;&nbsp;&nbsp;
	    				<select name="AF_options[AF_link_font]" id="AF_options[AF_link_font]">
	    					<option value="none" <?php if ($options['AF_link_font'] == "none") echo "selected"; ?>>Garder la police de mon site</option>
	    					
	    					<option value="'Arial', Helvetica, sans-serif" <?php if ($options['AF_link_font'] == "'Arial', Helvetica, sans-serif") echo "selected"; ?>>'Arial', Helvetica, sans-serif</option>
	    					
	    					<option value="'Times New Roman', Times, serif" <?php if ($options['AF_link_font'] == "'Times New Roman', Times, serif") echo "selected"; ?>>'Times New Roman', Times, serif</option>
	    					
	    					<option value="'Courier New', Courier, mono" <?php if ($options['AF_link_font'] == "'Courier New', Courier, mono") echo "selected"; ?>>'Courier New', Courier, mono</option>
	    					
	    				</select>
	    				
	    		<p>
	    		<label for="AF_options[AF_link_color]" style="margin-top: -15px; display: inline-block;">Couleur</label> &nbsp;
	    		<input name="AF_options[AF_link_color]" style="position: relative; top: 9px;" type='text' class='color-field' value="<?php if(isset($options['AF_link_color'])) { echo $options['AF_link_color']; }?> ">
	    		</p>
	    		
	    
	    
	    

		
		<p>
		<label for="AF_options[AF_bg_color]" style="margin-top: -15px; display: inline-block;">Couleur de fond</label> &nbsp;
		<input name="AF_options[AF_bg_color]" type='text' class='color-field' value="<?php if(isset($options['AF_bg_color'])) { echo $options['AF_bg_color']; }?> ">
		
		</p>
		
		<hr />
		
		<p>
		<strong>Afficher un cadre autour des annonces</strong>&nbsp;&nbsp;&nbsp;
		<input type="radio" name="AF_options[AF_global_border]" value="1" <?php if ($options['AF_global_border'] == 1) echo "checked"?> /> Oui
		<input type="radio" name="AF_options[AF_global_border]" value="0" <?php if ($options['AF_global_border'] == 0) echo "checked" ?> /> Non
		
		
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="AF_options[AF_global_border_type]">Style du cadre</label>&nbsp;&nbsp;&nbsp;
		<select name="AF_options[AF_global_border_type]" id="AF_options[AF_global_border_type]">

			<option value="solid" <?php if ($options['AF_global_border_type'] == 'solid') echo "selected"; ?>>Ligne pleine</option>
			<option value="dashed" <?php if ($options['AF_global_border_type'] == 'dashed') echo "selected"; ?>>Tirets</option>
			<option value="dotted" <?php if ($options['AF_global_border_type'] == 'dotted') echo "selected"; ?>>Pointillés</option>

		</select>
		
		</p>
		<p>
		
		Position du cadre &nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="AF_options[AF_global_border_top]" id="AF_options[AF_global_border_top]" value="1" <?php if ($options['AF_global_border_top'] == 1) { echo "checked"; } ?>>
		<label for="AF_options[AF_global_border_top]">Haut</label>&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="AF_options[AF_global_border_bottom]" id="AF_options[AF_global_border_bottom]" value="1" <?php if ($options['AF_global_border_bottom'] == 1) { echo "checked"; } ?>>
		<label for="AF_options[AF_global_border_bottom]">Bas</label>&nbsp;&nbsp;&nbsp;
		
		<input type="checkbox" name="AF_options[AF_global_border_left]" id="AF_options[AF_global_border_left]" value="1" <?php if ($options['AF_global_border_left'] == 1) { echo "checked"; } ?>>
		<label for="AF_options[AF_global_border_left]">Gauche</label>&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="AF_options[AF_global_border_right]" id="AF_options[AF_global_border_right]" value="1" <?php if ($options['AF_global_border_right'] == 1) { echo "checked"; } ?>>
		<label for="AF_options[AF_global_border_right]">Droite</label>&nbsp;&nbsp;&nbsp;
		
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="AF_options[AF_global_border_size]">Epaisseur du cadre</label>&nbsp;&nbsp;&nbsp;
		<select name="AF_options[AF_global_border_size]" id="AF_options[AF_global_border_size]">
		<?php 
		for ($i=1; $i<11; $i++) {
		?>
			<option value="<?php echo $i;?>" <?php if ($options['AF_global_border_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
		<?php
		}
		?>
		</select>
		
		</p>

		<p>
		<label for="AF_options[AF_global_border_color]" style="margin-top: -15px; display: inline-block;">Couleur du cadre</label> &nbsp;
		<input name="AF_options[AF_global_border_color]" type='text' class='color-field' value="<?php if(isset($options['AF_global_border_color'])) { echo $options['AF_global_border_color']; }?> ">		

		</p>
		
		<hr />
		<?php // BORDURE DES ANNONCES ?>
				<p>
				<strong>Séparer les annonces par une bordure</strong>&nbsp;&nbsp;&nbsp;
				<input type="radio" name="AF_options[AF_border]" value="1" <?php if ($options['AF_border'] == 1) echo "checked"?> /> Oui
				<input type="radio" name="AF_options[AF_border]" value="0" <?php if ($options['AF_border'] == 0) echo "checked" ?> /> Non
				
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label for="AF_options[AF_border_type]">Style du cadre</label>&nbsp;&nbsp;&nbsp;
				<select name="AF_options[AF_border_type]" id="AF_options[AF_border_type]">
		
					<option value="solid" <?php if ($options['AF_border_type'] == 'solid') echo "selected"; ?>>Ligne pleine</option>
					<option value="dashed" <?php if ($options['AF_border_type'] == 'dashed') echo "selected"; ?>>Tirets</option>
					<option value="dotted" <?php if ($options['AF_border_type'] == 'dotted') echo "selected"; ?>>Pointillés</option>
		
				</select>
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label for="AF_options[AF_border_size]">Epaisseur de la bordure</label>&nbsp;&nbsp;&nbsp;
				<select name="AF_options[AF_border_size]" id="AF_options[AF_border_size]">
				<?php 
				for ($i=1; $i<11; $i++) {
				?>
					<option value="<?php echo $i;?>" <?php if ($options['AF_border_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
				<?php
				}
				?>
				</select>
				
				</p>
				<p>
				<?php /* DISABLED OPTIONS
				Position de la bordure &nbsp;&nbsp;&nbsp;
						<input type="checkbox" name="AF_options[AF_border_top]" id="AF_options[AF_border_top]" value="1" <?php if ($options['AF_border_top'] == 1) { echo "checked"; } ?>>
						<label for="AF_options[AF_border_top]">Haut</label>&nbsp;&nbsp;&nbsp;
						<input type="checkbox" name="AF_options[AF_border_bottom]" id="AF_options[AF_border_bottom]" value="1" <?php if ($options['AF_border_bottom'] == 1) { echo "checked"; } ?>>
						<label for="AF_options[AF_border_bottom]">Bas</label>&nbsp;&nbsp;&nbsp;
						
						<input type="checkbox" name="AF_options[AF_border_left]" id="AF_options[AF_border_left]" value="1" <?php if ($options['AF_border_left'] == 1) { echo "checked"; } ?>>
						<label for="AF_options[AF_border_left]">Gauche</label>&nbsp;&nbsp;&nbsp;
						<input type="checkbox" name="AF_options[AF_border_right]" id="AF_options[AF_border_right]" value="1" <?php if ($options['AF_border_right'] == 1) { echo "checked"; } ?>>
						<label for="AF_options[AF_border_right]">Droite</label>&nbsp;&nbsp;&nbsp;
						
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<label for="AF_options[AF_border_size]">Epaisseur du cadre</label>&nbsp;&nbsp;&nbsp;
						<select name="AF_options[AF_border_size]" id="AF_options[AF_border_size]">
						<?php 
						for ($i=1; $i<11; $i++) {
						?>
							<option value="<?php echo $i;?>" <?php if ($options['AF_border_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
						<?php
						}
						?>
						</select>
						
						</p>
				*/ ?>
						<p>
						<label for="AF_options[AF_border_color]" style="margin-top: -15px; display: inline-block;">Couleur du cadre</label> &nbsp;
						<input name="AF_options[AF_border_color]" type='text' class='color-field' value="<?php if(isset($options['AF_border_color'])) { echo $options['AF_border_color']; }?> ">		
				
						</p>
				
								
				
		</p>
		
		
		
		
		<hr />
		<p>
		<label for="AF_options[AF_disclaimer_text]"><strong>Texte à afficher avec les annonces</strong></label>&nbsp;&nbsp;&nbsp;
		<select name="AF_options[AF_disclaimer_text]" id="AF_options[AF_disclaimer_text]">
			<option value="Liens partenaires" <?php if ($options['AF_disclaimer_text'] == 'Liens partenaires') echo "selected"; ?>>Liens partenaires</option>
			<option value="Liens sponsorisés" <?php if ($options['AF_disclaimer_text'] == 'Liens sponsorisés') echo "selected"; ?>>Liens sponsorisés</option>

		</select>
		</p>
		<p>
		<label for="AF_options[AF_disclaimer_color]" style="margin-top: -15px; display: inline-block;">Couleur du texte</label> &nbsp;
		<input name="AF_options[AF_disclaimer_color]" style="position: relative; top: 9px;" type='text' class='color-field' value="<?php if(isset($options['AF_disclaimer_color'])) { echo $options['AF_disclaimer_color']; }?> ">
		
		</p>
		<p>
		<label for="AF_options[AF_disclaimer_size]">Taille</label>&nbsp;&nbsp;&nbsp;
		<select name="AF_options[AF_disclaimer_size]" id="AF_options[AF_disclaimer_size]">
		<?php 
		for ($i=8; $i<16; $i++) {
		?>
			<option value="<?php echo $i;?>" <?php if ($options['AF_disclaimer_size'] == $i) echo "selected"; ?>><?php echo $i; ?> px</option>
		<?php
		}
		?>
		</select>
		&nbsp;&nbsp;&nbsp;
		<label for="AF_options[AF_disclaimer_font]">Police</label>&nbsp;&nbsp;&nbsp;
				<select name="AF_options[AF_disclaimer_font]" id="AF_options[AF_disclaimer_font]">
					<option value="none" <?php if ($options['AF_disclaimer_font'] == "none") echo "selected"; ?>>Garder la police de mon site</option>
					
					<option value="'Arial', Helvetica, sans-serif" <?php if ($options['AF_disclaimer_font'] == "'Arial', Helvetica, sans-serif") echo "selected"; ?>>'Arial', Helvetica, sans-serif</option>
					
					<option value="'Times New Roman', Times, serif" <?php if ($options['AF_disclaimer_font'] == "'Times New Roman', Times, serif") echo "selected"; ?>>'Times New Roman', Times, serif</option>
					
					<option value="'Courier New', Courier, mono" <?php if ($options['AF_disclaimer_font'] == "'Courier New', Courier, mono") echo "selected"; ?>>'Courier New', Courier, mono</option>
					
				</select>
		&nbsp;&nbsp;&nbsp;
		<label for="AF_options[AF_disclaimer_position]">Position</label>&nbsp;&nbsp;&nbsp;
				<select name="AF_options[AF_disclaimer_position]" id="AF_options[AF_disclaimer_position]">
		
					<option value="TL" <?php if ($options['AF_disclaimer_position'] == 'TL') echo "selected"; ?>>En haut à gauche</option>
					<option value="TR" <?php if ($options['AF_disclaimer_position'] == 'TR') echo "selected"; ?>>En haut à droite</option>
					<option value="BL" <?php if ($options['AF_disclaimer_position'] == 'BL') echo "selected"; ?>>En bas à gauche</option>
					<option value="BR" <?php if ($options['AF_disclaimer_position'] == 'BR') echo "selected"; ?>>En bas à droite</option>
					
				</select>
		</p>
		
				
		<hr />
				
		<p>
		
		<strong>Utiliser une feuille de style personnalisée&nbsp;&nbsp;</strong>
		<input type="radio" name="AF_options[AF_custom_styles]" value="0" <?php if ($options['AF_custom_styles'] == '0') echo "checked"?> /> Non
		<input type="radio" name="AF_options[AF_custom_styles]" value="1" <?php if ($options['AF_custom_styles'] == '1') echo "checked" ?> /> Oui
		<br />
		<code>Si vous choisissez "OUI", les réglages de présentation définis ici ne seront pas pris en compte.<br />ATTENTION: Si vous décidez d'utiliser les styles personnalisés, vous devrez ajouter les styles CSS nécessaire au bon affichage des annonces manuellement dans la feuille de style de votre thème wordpress.</code>
		
		</p>
		
		
		<p>
			    <input type="submit" value="Enregistrer les options" class="button button-primary" name="submit" />
		</p>
		</p>	    
	    </form>
	</div>    
	
	
	<?php
	// print_r($options);

}




////////////////////////////////////////////////////////
// 			FRONT
////////////////////////////////////////////////////////


// calls de adfever_links func with post data passed from ajax with ajax flag true
function AFajaxcallback($die=true) {
	$options = get_option('AF_options');
	if ($options['AF_act_lienssponsos'] == 1 && $options['AF_AID'] != '') {
		adfever_links($_POST['theID'],'', $_POST['options'], true);
	}
	die();
 }

function AF_theme_callback($postID ='') {
	if ($postID != '' && is_numeric($postID)){
		AF_add_outer('',$postID);
	} else {
		echo "Invalid or missing Post ID";
	}
}

// just a little function to remove spaces from some variables
function remspace($string) {
	$string = preg_replace('/\s+/', '', $string);
	return $string;	
}

// Here we generate the custom CSS from option pages. - dynamically adds style block in header - faster than loading pregenerated or dynamic CSS
function adfever_generate_css() {
	$options = get_option('AF_options');
	if ($options['AF_act_lienssponsos'] == 1 && $options['AF_AID'] != '') {
		if ($options['AF_custom_styles'] == 0) {
		$output = '<style type="text/css">';
		
		//here we add the global AF block style
		$output .= '#adfeverlinks {';
		if ($options['AF_bg_color'] != '') {
			$output .=  'background-color: '.$options['AF_bg_color'].';';
		}
		// we add the global border if needed
		if ($options['AF_global_border'] != 1) {
				$output .= 'border: none;';
		}
		else {
			if ($options['AF_global_border_top'] == 1) {
		 		$output .= "border-top: ".$options['AF_global_border_size']."px ".$options['AF_global_border_type']." ".$options['AF_global_border_color'].";";
		 	}
		 	if ($options['AF_global_border_left'] == 1) {
		 			$output .= "border-left: ".$options['AF_global_border_size']."px ".$options['AF_global_border_type']." ".$options['AF_global_border_color'].";";
		 	}
		 	if ($options['AF_global_border_right'] == 1) {
		 			$output .= "border-right: ".$options['AF_global_border_size']."px ".$options['AF_global_border_type']." ".$options['AF_global_border_color'].";";
		 	}
		 	if ($options['AF_global_border_bottom'] == 1) {
		 			$output .= "border-bottom: ".$options['AF_global_border_size']."px ".$options['AF_global_border_type']." ".$options['AF_global_border_color'].";";
		 	}
		}
		
		// we close the global style
		$output .= '} ';
		
		// disclaimer text style
		$output .= '.AF_disclaimer { ';
		if($options['AF_disclaimer_color'] != '') {
			$output .= "color: ".remspace($options['AF_disclaimer_color'])."; ";
		}
		$output .= " font-size: ".remspace($options['AF_disclaimer_size'])."px ; ";
		if ($options['AF_disclaimer_position'] == 'TL' || $options['AF_disclaimer_position'] == 'BL') {
			$output .= 'text-align: left; ';
		} else {
			$output .= 'text-align: right; ';
		}
		if ($options['AF_disclaimer_font'] != 'none') {
			$output .= "font-family: ".$options['AF_disclaimer_font']."; ";
		}
		
		// we close the disclaimer styles
		$output .= '} ';
		
		// ads title styles
		$output .= '#adfeverlinks a.AF_title { ';
		if($options['AF_title_color'] != '') {
			$output .= "color: ".remspace($options['AF_title_color'])."; ";
		}
		$output .= " font-size: ".remspace($options['AF_title_size'])."px ; ";
		if ($options['AF_title_font'] != 'none') {
			$output .= "font-family: ".$options['AF_title_font']."; ";
		}
		$output .= "} ";
		
		// ads text styles
		$output .= '#adfeverlinks a.AF_text { ';
		if($options['AF_text_color'] != '') {
			$output .= "color: ".remspace($options['AF_text_color'])."; ";
		}
		$output .= " font-size: ".remspace($options['AF_text_size'])."px ; ";
		if ($options['AF_title_font'] != 'none') {
			$output .= "font-family: ".$options['AF_text_font']."; ";
		}
		$output .= "} ";
		
		// ads title styles
		$output .= '#adfeverlinks a.AF_link { ';
		if($options['AF_link_color'] != '') {
			$output .= "color: ".remspace($options['AF_link_color'])."; ";
		}
		$output .= " font-size: ".remspace($options['AF_link_size'])."px ; ";
		if ($options['AF_link_font'] != 'none') {
			$output .= "font-family: ".$options['AF_link_font']."; ";
		}
		$output .= "} ";
		
		// adds list item style (for individual ads borders
		$output .= '#adfeverlinks li { ';
		if ($options['AF_border'] == 0) {
				$output .= 'border: none;';
		}
		else {
		 	$output .= "border-top: ".$options['AF_border_size']."px ".$options['AF_border_type']." ".$options['AF_border_color'].";";
		}
		$output .= '}';
		$output .= '#adfeverlinks li:first-child { border: none;}';
		
		$output .= '</style>';
		echo $output;
		}
	}
}


//$AF_results = '';
//$AF_error = '';

// we add an outer div with the post ID so we can retrieve that ID in our JS
function AF_add_outer($output='', $post_id='') {
	if ($post_id == '') {
		$post_id = get_the_ID();
	}
	$AF_options = get_option('AF_options');
	if ($AF_options['AF_act_lienssponsos'] == 1 && $AF_options['AF_AID'] != '') {
		$ads = '<div class="adfeverlinks entry" id="adfever-'.$post_id.'"></div>';
		if ($AF_options['AF_autoshow_beforepost'] == 1 || $AF_options['AF_autoshow_afterpost'] == 1) {
			if ($AF_options['AF_autoshow_beforepost'] == 1) {
				$output = $ads.' '.$output;
			}
			if ($AF_options['AF_autoshow_afterpost'] == 1) {
				$output = $output.' '.$ads;
			}
			return $output;
		} else {
			// no autoshow enabled, we simply echo the adfever outer div
			echo $ads;
		}	
	}
}

function adfever_links($theID ='', $output = '', $AF_options='', $ajax=false){
	//  $debug = true;
	if ($ajax) {
		global $wpdb;
	}
	// echo $theID;
	if ($theID != '') {
		if ($AF_options == '') {
			$AF_options = get_option( 'AF_options' );
		}
		// we retrieve the post's tags
		$posttags = get_the_tags($theID);
		$tag = '';
		if ($posttags) {
		  foreach($posttags as $tag) {
		    $tags .= $tag->name . ' '; 
		  }
		}
		if ($debug) { echo "--- TAGS: ".$tags; }
		// we add the title - if no or few tags, it'll use the title as well
		$tags .= get_the_title($theID);
		// we cut the request down to adfever limit
		$request = substr($tags, 0, 100);
		
		if ($debug) {  echo " --- REQUEST: ".$request; }
		
		// Options adFeverSearch
		$AF_AID = $AF_options['AF_AID']; //"8640";
		$AF_SID = $AF_options['AF_SID']; //20844";
		// adfever search query
		$AF_search_settings = array ( 'start' => "0" , 'size' => (string)$AF_options['AF_num_to_display'] , "sid" => $AF_SID, "aid"=> $AF_AID, "adult" => "YES") ;
		$AF_search = new AdFever ( $AF_search_settings ) ;
		// setlocale ( LC_ALL, 'fr', 'fr_FR' ) ;
		// $request = preg_replace ( '/[^\w]/', ' ', $request ) ;
		//$request = preg_replace ( '/\s\s+/', ' ', $request ) ;
		$AF_search->search ( $request ) ;		
		if ($debug) { 
			// print_r($AF_search); 
			echo "---REQUEST: ".$request;
			echo "--- TOTAL RESULTS: ".($AF_search->total)."--- XML RESULTS :".$AF_search->return_total;
		}
		// we get the search results
		if ($AF_search->total > 0) {
			// $AF_results = 'Annonce '. (1). ' à '. ($AF_search->return_total). ' sur '. $AF_search->total ;
			$AF_results .= "<div id='adfeverlinks'>";
			if ($AF_options['AF_disclaimer_position'] == 'TL' || $AF_options['AF_disclaimer_position'] == 'TR') {
				$AF_results .= "<span class='AF_disclaimer'>".$AF_options['AF_disclaimer_text']."</span>";
			}
			$AF_results .= "<ul>";
			foreach ( $AF_search->listings as $listing ) {
				$AF_results .= "<li><a href='" . $listing [ 'redirect_url' ] . "' target='_blank' rel='nofollow' class='AF_title'>" . html_entity_decode($listing [ 'title' ])."</a>" ;
				$AF_results .= "<br /><a href='" . $listing [ 'redirect_url' ] . "' target='_blank' rel='nofollow' class='AF_text'>" . html_entity_decode($listing [ 'description' ])  . "</a><br />" ;
				$AF_results .= "<a href='" . $listing [ 'redirect_url' ] . "' target='_blank' rel='nofollow' class='AF_link'>".$listing [ 'site_url' ] . "</a></li>" ;
			}
			$AF_results .= "</ul>";
			if ($AF_options['AF_disclaimer_position'] == 'BL' || $AF_options['AF_disclaimer_position'] == 'BR') {
				$AF_results .= "<span class='AF_disclaimer'>".$AF_options['AF_disclaimer_text']."</span>";
			}
			$AF_resuluts .= "</div>";
		}
		// check if error, else displays the result 
		if (count ( $AF_search->errors ) > 0) {
			foreach ( $AF_search->errors as $err ) {
				$AF_error = $err ;
				if ($debug) { echo " --- ERROR: ".$AF_error; }
			}
		} else {
			if (!$ajax) {
				//$AF_results = trim($AF_results, -1);
			}
		echo $AF_results;
		}
	}
}




?>