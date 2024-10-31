<?php

namespace RevisedStatus\View;
use RevisedStatus\Base;
use RevisedStatus\Controller\Options as OptionsController;
use RevisedStatus\Controller\RevisionManager;

/**
 * Class RevisionMetabox
 *
 * @method static RevisionMetabox getInstance
 *
 * @package RevisedStatus
 */
class RevisionMetabox {
	use Base;

	/**
	 * Basic setup
	 */
	function setup() {
		add_action( 'add_meta_boxes', [ $this, 'set_metaboxes' ] );
	}


	/**
	 * Sets up the metaboxes for the option page.
	 */
	function set_metaboxes() {

		$opt_controller = OptionsController::getInstance();

		$options = $opt_controller->get_options();

		$screen = get_current_screen();

		// Display the our metabox instad of the default one if:
		// * revise_options for the current post type is enabled
		// OR
		// if track all posttypes is enabled
		// but this particular posttype isn't disabled explicitly
		if ( isset( $options[ 'revise_' . $screen->id ] )
		     || ( $options['track_all_posttypes'] &&
		          ( isset( $options['disabled'] ) && is_array( $options['disabled'] ) && ! in_array( $screen->id, $options['disabled'] ) ) ||
		          ! isset( $options['disabled'] ) || ! is_array( $options['disabled'] ) )
		) {
			add_meta_box( 'wpsr_status_revised',
				__( 'Revisions (with publication status history)', WP_REVSTATUS_SLUG ),
				[ $this, 'render_metabox' ],
				null, // todos los posttypes
				'normal',
				'default' );

			remove_meta_box( 'revisionsdiv', null, 'normal' );

			return true;
		}

		return false;
	}

	/**
	 * Display list of a post's revisions.
	 *
	 * Can output either a UL with edit links or a TABLE with diff interface, and
	 * restore action links.
	 *
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global $post.
	 * @param string      $type 'all' (default), 'revision' or 'autosave'
	 *
	 * @return null
	 */
	function render_metabox( $post_id = 0, $type = 'all' ) {
		if ( ! $post = get_post( $post_id ) ) {
			return;
		}

		if ( ! $revisions = wp_get_post_revisions( $post->ID ) ) {
			return;
		}

		$rows = '';
		foreach ( $revisions as $revision ) {
			if ( ! current_user_can( 'read_post', $revision->ID ) ) {
				continue;
			}

			$is_autosave = wp_is_post_autosave( $revision );
			if ( ( 'revision' === $type && $is_autosave ) || ( 'autosave' === $type && ! $is_autosave ) ) {
				continue;
			}

			$rows .= "\t<li>" . $this->render_title( $revision ) . "</li>\n";
		}

		echo "<div class='hide-if-js'><p>" . __( 'JavaScript must be enabled to use this feature.', WP_REVSTATUS_SLUG )
		     . "</p></div>\n";

		echo "<ul class='post-revisions hide-if-no-js'>\n";
		echo $rows;
		echo "</ul>";
	}

	/**
	 * Retrieve formatted date timestamp of a revision (linked to that revisions's page).
	 *
	 * @param int|object $revision Revision ID or revision object.
	 * @param bool       $link Optional, default is true. Link to revisions's page?
	 *
	 * @return string gravatar, user, i18n formatted datetimestamp or localized 'Current Revision'.
	 */
	function render_title( $revision, $link = true ) {
		if ( ! $revision = get_post( $revision ) ) {
			return $revision;
		}

		if ( ! in_array( $revision->post_type, array( 'post', 'page', 'revision' ) ) ) {
			return false;
		}

		$author = get_the_author_meta( 'display_name', $revision->post_author );
		/* translators: revision date format, see http://php.net/date */
		$datef = _x( 'F j, Y @ H:i:s', 'revision date format' );

		$gravatar = get_avatar( $revision->post_author, 24 );

		$date = date_i18n( $datef, strtotime( $revision->post_modified ) );
		if ( $link && current_user_can( 'edit_post', $revision->ID ) && $link = get_edit_post_link( $revision->ID ) ) {
			$date = "<a href='$link'>$date</a>";
		}

		$revision_date_author = sprintf(
		/* translators: post revision title: 1: author avatar, 2: author name, 3: time ago, 4: date */
			_x( '%1$s %2$s, %3$s ago (%4$s)', 'post revision title' ),
			$gravatar,
			$author,
			human_time_diff( strtotime( $revision->post_modified ), current_time( 'timestamp' ) ),
			$date
		);

		$autosavef = __( '%1$s [Autosave]' );
		$currentf  = __( '%1$s [Current Revision]' );

		if ( ! wp_is_post_revision( $revision ) ) {
			$revision_date_author = sprintf( $currentf, $revision_date_author );
		} elseif ( wp_is_post_autosave( $revision ) ) {
			$revision_date_author = sprintf( $autosavef, $revision_date_author );
		}

		$rm = RevisionManager::getInstance();

		$rev_status = $rm->get_status( $revision->ID );
		$rev_status = get_post_status_object( $rev_status );

		$rev_status = isset( $rev_status->label ) ? $rev_status->label : __( 'Not tracked', WP_REVSTATUS_SLUG );

		return $revision_date_author . " - [$rev_status]";
	}

}