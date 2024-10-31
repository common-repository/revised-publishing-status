<?php
namespace RevisedStatus\View;
use RevisedStatus\Base;

/**
 * Class Options
 *
 * @method static Options getInstance
 *
 * @package RevisedStatus
 */
class Options {
	use Base;

	/**
	 * Empty constructor. Nothing going on.
	 */
	public function __construct() {

	}

	/**
	 * Renders the option page for the plugin
	 */
	public function render_options_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Publishing status tracking options',
					WP_REVSTATUS_SLUG ); ?></h2>

			<form action="options.php" method="post">
				<?php
				settings_fields( WP_REVSTATUS_SETTINGS );
				do_settings_sections( WP_REVSTATUS_SETTINGS );
				submit_button( __( 'Save', WP_REVSTATUS_SLUG ) );
				?>
			</form>

		</div>
		<div class="wrap">
			<h3><?php _e( 'Filter Hooks available', WP_REVSTATUS_SLUG ); ?></h3>

			<p><?php _e( 'Options set via hooks supersede options chosen in this page', WP_REVSTATUS_SLUG ); ?></p>

			<dl>
				<dt><code>wp-revised-status_track-all</code></dt>
				<dd><p><?php _e( 'Sets up tracking for all available versioned post-types. Return <em>true</em> to activate.',
						WP_REVSTATUS_SLUG ); ?></p>
				<p>E.g.:</p>
					<pre class="prettyprint linenums"><code class="lang-php">
add_filter( 'wp-revised-status_track-all', '__return_true' );</code></pre>
				</dd>
				<dt><code>wp-revised-status_tracked-posttypes</code></dt>
				<dd>
					<p><?php _e( 'Return an associative array in this filter to activate tracking to your chosen post types. e.g:',
							WP_REVSTATUS_SLUG ); ?></p>

					<p>E.g.:</p>
					<pre class="prettyprint linenums"><code class="lang-php">
add_filter( 'wp-revised-status_tracked-posttypes', function ( $i ) {
$i['post'] = 1;

return $i;
} );</code></pre>

					<p><?php _e( 'Redundant with the previous filter', WP_REVSTATUS_SLUG ); ?></p></dd>
				<dt><code>wp-revised-status_untracked-posttypes</code></dt>
				<dd>
					<p><?php _e( 'Return an associative array in this filter to disable tracking for your chosen post types. e.g:',
							WP_REVSTATUS_SLUG ); ?></p>

					<p>E.g.: </p>
						<pre class="prettyprint linenums"><code class="lang-php">
add_filter( 'wp-revised-status_tracked-posttypes', 'my_theme_disable_tracking' );
function my_theme_disable_tracking($i) {
	$i['post'] = 1;
	return $i;
}</code></pre>
					<p><?php _e( 'Trumps all other options or filters', WP_REVSTATUS_SLUG ); ?></p>
				</dd>
			</dl>

			<script
				src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?autoload=true&amp;skin=desert&amp;lang=css"
				defer="defer"></script>

		</div>
		<?php
	}


	/**
	 * Renders the main settings section for the plugin, including a warning in case there are
	 * hooked options
	 *
	 * @param $section
	 */
	public function render_section_posttypes( $section ) {
		$control = \RevisedStatus\Controller\Options::getInstance();

		$enabled  = $control->getEnabled();
		$disabled = $control->getDisabled();

		$enabled  = ! empty( $enabled );
		$disabled = ! empty( $disabled );
		$trackAll = $control->getTrackAll();

		// If there are posttypes enabled or disabled by use of the appropriate hook display a user notice.
		if ( $enabled || $disabled || $trackAll ) {
			echo "<div id='message' class='updated'>"
			     . __( 'Attention: Settings for this plugin have been modified by use of actions hooks, done from within a theme or plugin:',
					WP_REVSTATUS_SLUG );
			echo "</div>";
		}
	}


	/**
	 * Generic checkbox for the posttypes enablers.
	 *
	 * @param $args
	 */
	public function render_checkbox( $args ) {
		if ( isset( $args['id'] ) ) {
			$id = $args['id'];
		} else {
			return;
		}

		$control  = \RevisedStatus\Controller\Options::getInstance();
		$settings = get_option( WP_REVSTATUS_SETTINGS );

		$hook_enabled  = $control->getEnabled( $id );
		$hook_disabled = $control->getDisabled( $id );

		$is_hooked   = $hook_disabled || $hook_enabled;
		$all_options = $control->getTrackAll() && 'track_all_posttypes' === $id;

		$checked  = checked( isset( $settings[ $id ] ), true, false );
		$disabled = $is_hooked || $all_options ? 'disabled="disabled"' : '';

		$memo = '';
		if ( $hook_enabled || $all_options ) {
			$memo = __( 'Warning, this setting has been activated by a plugin hook.', WP_REVSTATUS_SLUG );
		}
		if ( $hook_disabled ) {
			$memo = __( "Warning, tracking of this post type has been disabled by a plugin hook.", WP_REVSTATUS_SLUG );
		}

		echo "<input type='checkbox' id='" . WP_REVSTATUS_SETTINGS . "' name='"
		     . WP_REVSTATUS_SETTINGS
		     . "[$id]' size='40' value='1' $checked $disabled /> $memo";

		if ( $checked && $disabled || $all_options ) {
			echo "<input type='hidden' value='1' name='" . WP_REVSTATUS_SETTINGS . "[$id]' >\n";
		}

	}


}