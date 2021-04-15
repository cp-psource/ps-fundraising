<?php

/***
 * You can hook in ajax functions in WordPress/BuddyPress by using the 'wp_ajax' action.
 * 
 * When you post your ajax call from javascript using jQuery, you can define the action
 * which will determin which function to run in your PHP component code.
 *
 * Here's an example:
 *
 * In Javascript we can post an action with some parameters via jQuery:
 * 
 * 			jQuery.post( ajaxurl, {
 *				action: 'my_example_action',
 *				'cookie': encodeURIComponent(document.cookie),
 *				'parameter_1': 'some_value'
 *			}, function(response) { ... } );
 *
 * Notice the action 'my_example_action', this is the part that will hook into the wp_ajax action.
 * 
 * You will need to add an add_action( 'wp_ajax_my_example_action', 'the_function_to_run' ); so that
 * your function will run when this action is fired.
 * 
 * You'll be able to access any of the parameters passed using the $_POST variable.
 *
 * Below is an example of the addremove_friend AJAX action in the friends component.
 */

/***
 * NOTE:
 * Try and avoid returning HTML layout in your ajax functions.
 */

function wdf_friends_ajax_addremove_friend() {
	global $bp;

	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {

		if ( !friends_remove_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {
			echo __( 'Die Freundschaft konnte nicht gekündigt werden.', 'bp-component' );
		} else {
			echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Freund hinzufügen', 'wdf' ) . '" href="' . $bp->loggedin_user->domain . $bp['friends']['slug'] . '/add-friend/' . $_POST['fid'] . '">' . __( 'Freund hinzufügen', 'wdf' ) . '</a>';
		}

	} else if ( 'not_friends' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {

		if ( !friends_add_friend( $bp->loggedin_user->id, $_POST['fid'] ) ) {
			echo __( 'Freundschaft konnte nicht angefordert werden.', 'wdf');
		} else {
			echo '<a href="' . $bp->loggedin_user->domain . $bp['friends']['slug'] . '" class="requested">' . __( 'Freundschaftsanfrage', 'bp-component' ) . '</a>';
		}

	} else {
		echo __( 'Anfrage ausstehend', 'bp-component' );
	}
	
	return false;
}
//add_action( 'wp_ajax_addremove_friend', 'friends_ajax_addremove_friend' );

?>