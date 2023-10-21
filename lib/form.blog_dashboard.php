<div id="wdf_dashboard" class="wrap">
	<div id="icon-wdf-admin" class="icon32"><br></div>
	<h2 id="wdf-getting-started"><?php _e('Erste Schritte','wdf'); ?></h2>
	<p><?php _e('Das Fundraising-Plugin kann Dir helfen, Deine wichtigen Projekte zu finanzieren.','wdf') ?></p>
	<div class="metabox-holder">

		<?php if(current_user_can('wdf_edit_settings')) { ?>
		<div class="postbox">
			<h3 class="hndle"><span><?php _e('Handbuch zur erstmaligen Einrichtung','wdf'); ?></span></h3>
			<div class="inside">
				<div class="updated below-h2">
					<p><?php _e('Willkommen beim Fundraising Plugin!','wdf'); ?></p>
				</div>
				<ol id="wdf_steps">
					<li><?php _e('Konfiguriere Deine Einstellungen so, dass einfache Spenden entgegengenommen werden, oder richte Vorauszahlungen ein, um eine eigene Crowdfunding-Seite zu erstellen.','wdf'); ?><a href="<?php echo admin_url('edit.php?post_type=funder&page=wdf_settings'); ?>" class="button wdf_goto_step"><?php _e('Einstellungen konfigurieren','wdf'); ?></a></li>
					<li><?php _e('Erstelle Deine erste Spendenaktion, lege ein Ziel fest und wähle einen Anzeigestil.','wdf'); ?><a href="<?php echo admin_url('post-new.php?post_type=funder'); ?>" class="button wdf_goto_step"><?php _e('Erstelle eine Spendenaktion','wdf'); ?></a></li>
					<li><?php _e('Wähle Deinen Präsentationsstil mithilfe der verfügbaren Widgets oder Shortcodes.','wdf'); ?><a href="<?php echo admin_url('widgets.php?wdf_show_widgets=1'); ?>" class="button wdf_goto_step"><?php _e('Alle Widgets anzeigen','wdf'); ?></a></li>
				</ol>
			</div>
		</div>
		<?php } ?>

		<div class="postbox">
			<h3 class="hndle"><span><?php _e('Verfügbare Shortcodes','wdf'); ?></span></h3>
			<div class="inside">
				<p class="wdf_shortcode_ss"><img src="<?php echo WDF_PLUGIN_URL . '/img/shortcode-generator-screenshot.jpg' ?>" /></p>
				<ul class="wdf_shortcode_breakdown">
					<li>
						<h4><strong><?php _e('Spendenaktion Panel','wdf'); ?></strong></h4>
						<p class="description"><?php _e('Das Spendenfeld zeigt relevante Informationen zu einer bestimmten Spendenaktion an, z.B.: Gesamte Aktionen, Zielinformationen und Links zur Checkout-Seite für Versprechen.','wdf'); ?></p>
						<code>[fundraiser_panel id="" style="" show_title="" show_content=""]</code>
						<p class="attr_description">id: <span class="description"><?php _e('Die ID der Spendenaktion, die Du anzeigen möchtest','wdf'); ?></span></p>
						<p class="attr_description">style: <span class="description"><?php _e('Ein gültiger geladener Stilname. Dies verwendet den Standard-Spendenstil, wenn kein Stil angegeben ist.','wdf'); ?></span></p>
						<p class="attr_description">show_title: <span class="description"><?php _e('(ja/nein) Zeigt den Titel der Spendenaktion über dem Panel an','wdf'); ?></span></p>
						<p class="attr_description">show_content: <span class="description"><?php _e('(ja/nein) Zeigt den Post-Inhalt der Spendenaktion über dem Panel an','wdf'); ?></span></p>
					</li>
					<li>
						<h4><strong><?php _e('Einfache Spendenschaltfläche','wdf'); ?></strong></h4>
						<p class="description"><?php _e('Mit der einfachen Spendenschaltfläche kannst Du einfache Paypal-Spenden mit einem Klick entgegennehmen.','wdf'); ?></p>
						<code>[donate_button title="" description="" donation_amount="" button_type="default/custom" style="" button_text="" show_cc="yes/no" small_button="yes/no" paypal_email=""]</code>
						<?php /*?>
						<p class="attr_description">title: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">description: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">donation_amount: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">button_type: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">style: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">button_text: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p>
						<p class="attr_description">type: <span class="description"><?php //_e('The type of donate_button to display.  paypal is the only type accepted at this time.','wdf'); ?></span></p><?php */?>
					</li>
					<li>
						<h4><strong><?php _e('Spendenaktion Fortschrittsleiste','wdf'); ?></strong></h4>
						<p class="description"><?php _e('Zeige einen Fortschrittsbalken für eine bestimmte Spendenaktion an.','wdf'); ?></p>
						<code>[progress_bar id="" style="" show_title="yes/no" show_totals="yes/no"]</code>
						<p class="attr_description">id: <span class="description"><?php _e('Die ID der Spendenaktion, für die Du einen Fortschrittsbalken anzeigen möchtest.','wdf'); ?></span></p>
						<p class="attr_description">style: <span class="description"><?php _e('Ein gültiger geladener Stilname. Dies verwendet den Standard-Spendenstil, wenn kein Stil angegeben ist.','wdf'); ?></span></p>
						<p class="attr_description">show_title: <span class="description"><?php _e('(yes/no) Zeigt den Titel der Spendenaktion über dem Fortschrittsbalken an - Standard: Nein','wdf'); ?></span></p>
						<p class="attr_description">show_totals: <span class="description"><?php _e('(yes/no) Zeigt das Spendenziel und den Betrag an, der über dem Fortschrittsbalken liegt. - Standard: nein','wdf'); ?></span></p>
					</li>
				</ul>
			</div>
		</div>

		<?php if(current_user_can('wdf_edit_settings')) { ?>
		<div class="postbox">
			<h3 class="hndle"><span><?php _e('Brauchst Du Hilfe?','wdf'); ?></span></h3>
			<div class="inside">
			<p class="description"><?php _e('Mehr Hilfe findest Du in unserem <a href="https://n3rds.work/piestingtal_source/ps-fundraising/" target="_blank" rel="noopener"><strong>PS FUNDRAISING SUPPORT FORUM</strong></a>.','wdf'); ?></p>				
			</div>
		</div>
		<?php } ?>

	</div>
</div><!-- #wdf_dashboard -->