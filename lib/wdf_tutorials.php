<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}?>
<?php
require_once( WDF_PLUGIN_BASE_DIR . '/lib/external/class.pointers_tutorial.php' );
		
$tutorial = new Pointer_Tutorial('wdf_tutorial', true, false);

if(isset($_POST['wdf_restart_tutorial']))
	$tutorial->restart();

$tutorial->set_textdomain = 'wdf';

$tutorial->add_style('');

$tutorial->set_capability = 'wdf_edit_settings';

$tutorial->add_step(admin_url('admin.php?page=wdf'), 'funder_page_wdf', '#wdf-getting-started', __('Der Einstieg ist einfach', 'wdf'), array(
		'content'  => '<p>' . esc_js( __('Befolge diese Tutorial-Schritte, um Dein Fundraising-Projekt schnell zum Laufen zu bringen.', 'wdf') ) . '</p>',
		'position' => array( 'edge' => 'top', 'align' => 'left' ),
	));
$tutorial->add_step(admin_url('edit.php?post_type=funder&page=wdf_settings&tab=payments'), 'funder_page_wdf_settings', '#wdf_settings_currency', __('Wähle Deine Währung', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Wähle Deine bevorzugte Währung für eingehenden Spenden.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'top', 'align' => 'left' ), 'post_type' => 'funder',
));
$tutorial->add_step(admin_url('edit.php?post_type=funder&page=wdf_settings&tab=payments'), 'funder_page_wdf_settings', '#wdf_allowed_fundraier_types', __('Wähle zulässige Zahlungsarten.', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Einfache Zahlungen wirken wie regelmäßige Spenden.  Erweiterte Zahlungen ermöglichen die Erstellung von Zielen und Belohnungen.  Erweiterte Zahlungen werden nur anfänglich genehmigt.  Genehmigte Zahlungen werden erst verarbeitet, wenn das Ziel erreicht wurde.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'left', 'align' => 'left' ), 'post_type' => 'funder',
));
if(!get_option('permalink_structure')) {
	$tutorial->add_step(admin_url('options-permalink.php'), 'options-permalink.php', '#permalink_structure', __('Permalinks aktivieren', 'wdf'), array(
		'content'  => '<p>' . esc_js( __('Permalinks müssen aktiviert und konfiguriert sein, bevor Deine Spendenseite öffentlich angezeigt werden kann.', 'wdf') ) . '</p>',
		'position' => array( 'edge' => 'top', 'align' => 'left' ),
	));
}
$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#titlediv', __('Erstelle Deine erste Spendenaktion', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Nachdem Du Deine Präsentations- und Zahlungseinstellungen eingerichtet hast, kannst Du Deine erste Spendenaktion erstellen. Füge zunächst einen Titel hinzu.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'top', 'align' => 'left' ), 'post_type' => 'funder',
));
$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#wdf_type', __('Wähle einen Spendentyp', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Dieser Schritt ist entscheidend für die Funktionsweise Deiner Spendenaktion. Denke daran: Einfache Spenden werden automatisch verarbeitet, können jedoch keine Ziele oder Belohnungen festlegen. Advanced Crowdfunding ermöglicht Ziele und Belohnungen, wird jedoch nur genehmigt, bis das Ziel erreicht wurde. Nachdem Du Deine Spendentyp gespeichert hast, hast Du abhängig von Deiner Wahl zusätzliche Optionen. Genießen!', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'right', 'align' => 'left' ), 'post_type' => 'funder',
));
/*$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#wdf_levels_table', __('Recommend Donation Levels', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('You can recommend donation levels to your visitors, provide a title, short description, and dollar amount for each level you create.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'bottom', 'align' => 'right' ), 'post_type' => 'funder',
));
$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#wdf_messages', __('Create Thank You Messages and Emails', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Send the user back to a specific url, any post or page ID, or enter a custom thank you message customizable with shortcodes.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'bottom', 'align' => 'right' ), 'post_type' => 'funder',
));	
$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#wdf_style', __('Choose A Style', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Choose a style that best fits your site, or apply no styles and use your own custom css.', 'wdf') ) . '</p>',
	'position' => array( 'edge' => 'right', 'align' => 'left' ), 'post_type' => 'funder',
));
$tutorial->add_step(admin_url('post-new.php?post_type=funder'), 'post-new.php', '#submitdiv', __('Publish or Save As Draft', 'wdf'), array(
	'content'  => '<p>' . esc_js( __('Publish your fundraiser, or save it as a draft.  Now start fundraising!  You can use the fundraiser url or insert the fundraising shortcodes directly into any page or post.', 'wdf')) . '</p>',
	'position' => array( 'edge' => 'right', 'align' => 'left' ), 'post_type' => 'funder',
));*/
$tutorial->initialize();
?>