/* global demoImporterLocalizeScript */
window.wp = window.wp || {};

( function( $ ) {

// Set up our namespace...
var demos, l10n;
demos = wp.demos = wp.demos || {};

// Store the demo data and settings for organized and quick access
// demos.data.settings, demos.data.demos, demos.data.l10n
demos.data = demoImporterLocalizeScript;
l10n = demos.data.l10n;

// Shortcut for isPreview check
demos.isPreview = !! demos.data.settings.isPreview;

// Shortcut for isInstall check
demos.isInstall = !! demos.data.settings.isInstall;

// Setup app structure
_.extend( demos, { model: {}, view: {}, routes: {}, router: {}, template: wp.template });

demos.Model = Backbone.Model.extend({
	// Adds attributes to the default data coming through the .org demos api
	// Map `id` to `slug` for shared code
	initialize: function() {
		var description;

		// If demo is already installed, set an attribute.
		if ( _.indexOf( demos.data.installedDemos, this.get( 'slug' ) ) !== -1 ) {
			this.set({ installed: true });
		}

		// Set the attributes
		this.set({
			// slug is for installation, id is for existing.
			id: this.get( 'slug' ) || this.get( 'id' )
		});

		// Map `section.description` to `description`
		// as the API sometimes returns it differently
		if ( this.has( 'sections' ) ) {
			description = this.get( 'sections' ).description;
			this.set({ description: description });
		}
	}
});

// Main view controller for demo importer
// Unifies and renders all available views
demos.view.Appearance = wp.Backbone.View.extend({

	el: '#wpbody-content .wrap .theme-browser',

	window: $( window ),
	// Pagination instance
	page: 0,

	// Sets up a throttler for binding to 'scroll'
	initialize: function( options ) {
		// Scroller checks how far the scroll position is
		_.bindAll( this, 'scroller' );

		this.SearchView = options.SearchView ? options.SearchView : demos.view.Search;
		// Bind to the scroll event and throttle
		// the results from this.scroller
		this.window.bind( 'scroll', _.throttle( this.scroller, 300 ) );
	},

	// Main render control
	render: function() {
		// Setup the main demo view
		// with the current demo collection
		this.view = new demos.view.Demos({
			collection: this.collection,
			parent: this
		});

		// Render search form.
		this.search();

		// Render and append
		this.view.render();
		this.$el.empty().append( this.view.el ).addClass( 'rendered' );
	},

	// Defines search element container
	searchContainer: $( '.search-form' ),

	// Search input and view
	// for current demo collection
	search: function() {
		var view,
			self = this;

		// Don't render the search if there is only one demo
		if ( demos.data.demos.length === 1 ) {
			return;
		}

		view = new this.SearchView({
			collection: self.collection,
			parent: this
		});

		// Render and append after screen title
		view.render();
		this.searchContainer
			.append( $.parseHTML( '<label class="screen-reader-text" for="wp-filter-search-input">' + l10n.search + '</label>' ) )
			.append( view.el );
	},

	// Checks when the user gets close to the bottom
	// of the mage and triggers a demo:scroll event
	scroller: function() {
		var self = this,
			bottom, threshold;

		bottom = this.window.scrollTop() + self.window.height();
		threshold = self.$el.offset().top + self.$el.outerHeight( false ) - self.window.height();
		threshold = Math.round( threshold * 0.9 );

		if ( bottom > threshold ) {
			this.trigger( 'demo:scroll' );
		}
	},

	// Remove any lingering tooltips and initialize TipTip
	initTipTip: function() {
		$( '#tiptip_holder' ).removeAttr( 'style' );
		$( '#tiptip_arrow' ).removeAttr( 'style' );
		$( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
	}
});

// Set up the Collection for our demo data
// @has 'id' 'name' 'screenshot' 'author' 'authorURI' 'version' 'active' ...
demos.Collection = Backbone.Collection.extend({

	model: demos.Model,

	// Search terms
	terms: '',

	// Controls searching on the current theme collection
	// and triggers an update event
	doSearch: function( value ) {

		// Don't do anything if we've already done this search
		// Useful because the Search handler fires multiple times per keystroke
		if ( this.terms === value ) {
			return;
		}

		// Updates terms with the value passed
		this.terms = value;

		// If we have terms, run a search...
		if ( this.terms.length > 0 ) {
			this.search( this.terms );
		}

		// If search is blank, show all demos
		// Useful for resetting the views when you clean the input
		if ( this.terms === '' ) {
			this.reset( demos.data.demos );
			$( 'body' ).removeClass( 'no-results' );
		}

		// Trigger a 'demos:update' event
		this.trigger( 'demos:update' );
	},

	// Performs a search within the collection
	// @uses RegExp
	search: function( term ) {
		var match, results, haystack, name, description, author;

		// Start with a full collection
		this.reset( demos.data.demos, { silent: true } );

		// Escape the term string for RegExp meta characters
		term = term.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' );

		// Consider spaces as word delimiters and match the whole string
		// so matching terms can be combined
		term = term.replace( / /g, ')(?=.*' );
		match = new RegExp( '^(?=.*' + term + ').+', 'i' );

		// Find results
		// _.filter and .test
		results = this.filter( function( data ) {
			name        = data.get( 'name' ).replace( /(<([^>]+)>)/ig, '' );
			description = data.get( 'description' ).replace( /(<([^>]+)>)/ig, '' );
			author      = data.get( 'author' ).replace( /(<([^>]+)>)/ig, '' );

			haystack = _.union( [ name, data.get( 'id' ), description, author, data.get( 'tags' ) ] );

			if ( match.test( data.get( 'author' ) ) && term.length > 2 ) {
				data.set( 'displayAuthor', true );
			}

			return match.test( haystack );
		});

		if ( results.length === 0 ) {
			this.trigger( 'query:empty' );
		} else {
			$( 'body' ).removeClass( 'no-results' );
		}

		this.reset( results );
	},

	// Paginates the collection with a helper method
	// that slices the collection
	paginate: function( instance ) {
		var collection = this;
		instance = instance || 0;

		// Demos per instance are set at 20
		collection = _( collection.rest( 20 * instance ) );
		collection = _( collection.first( 20 ) );

		return collection;
	}
});

// This is the view that controls each demo item
// that will be displayed on the screen
demos.view.Demo = wp.Backbone.View.extend({

	// Wrap demo data on a div.theme element
	className: 'theme',

	// Reflects which demo view we have
	// 'grid' (default) or 'detail'
	state: 'grid',

	// The HTML template for each element to be rendered
	html: demos.template( demos.isPreview ? 'demo-preview' : 'demo' ),

	events: {
		'click': 'expand',
		'keydown': 'expand',
		'touchend': 'expand',
		'keyup': 'addFocus',
		'touchmove': 'preventExpand',
		'click .demo-import': 'importDemo'
	},

	touchDrag: false,

	initialize: function() {
		this.model.on( 'change', this.render, this );
	},

	render: function() {
		var data = this.model.toJSON();

		// Render demos using the html template
		this.$el.html( this.html( data ) ).attr({
			tabindex: 0,
			'aria-describedby' : data.id + '-action ' + data.id + '-name',
			'data-slug': data.id
		});

		// Renders active demo styles
		this.activeDemo();

		if ( this.model.get( 'displayAuthor' ) ) {
			this.$el.addClass( 'display-author' );
		}
	},

	// Adds a class to the currently active demo
	// and to the overlay in detailed view mode
	activeDemo: function() {
		if ( this.model.get( 'active' ) ) {
			this.$el.addClass( 'active' );
		}
	},

	// Add class of focus to the demo we are focused on.
	addFocus: function() {
		var $demoToFocus = ( $( ':focus' ).hasClass( 'theme' ) ) ? $( ':focus' ) : $(':focus').parents('.theme');

		$('.theme.focus').removeClass('focus');
		$demoToFocus.addClass('focus');
	},

	// Single theme overlay screen
	// It's shown when clicking a theme
	expand: function( event ) {
		var self = this;

		// Prevent the modal.
		if ( demos.isPreview ) {
			return;
		}

		event = event || window.event;

		// 'enter' and 'space' keys expand the details view when a theme is :focused
		if ( event.type === 'keydown' && ( event.which !== 13 && event.which !== 32 ) ) {
			return;
		}

		// Bail if the user scrolled on a touch device
		if ( this.touchDrag === true ) {
			return this.touchDrag = false;
		}

		// Prevent the modal from showing when the user clicks
		// one of the direct action buttons
		if ( $( event.target ).is( '.theme-actions a' ) ) {
			return;
		}

		// Prevent the modal from showing when the user clicks one of the direct action buttons.
		if ( $( event.target ).is( '.theme-actions a, .update-message, .button-link, .notice-dismiss' ) ) {
			return;
		}

		// Set focused demo to current element
		demos.focusedDemo = this.$el;

		this.trigger( 'demo:expand', self.model.cid );
	},

	preventExpand: function() {
		this.touchDrag = true;
	},

	importDemo: function( event ) {
		var _this = this,
			$target = $( event.target );
		event.preventDefault();

		if ( $target.hasClass( 'disabled' ) ) {
			return;
		}

		// Confirmation dialog for importing a demo.
		if ( ! window.confirm( wp.demos.data.settings.confirmImport ) ) {
			return;
		}

		$( document ).on( 'wp-demo-import-success', function( event, response ) {
			if ( _this.model.get( 'id' ) === response.slug ) {
				_this.model.set( { 'imported': true } );
			}
		} );

		wp.updates.importDemo( {
			slug: $target.data( 'slug' )
		} );
	}
});

// Demo Details view
// Set ups a modal overlay with the expanded demo data
demos.view.Details = wp.Backbone.View.extend({

	// Wrap theme data on a div.theme element
	className: 'theme-overlay',

	events: {
		'click': 'collapse',
		'click .delete-demo': 'deleteDemo',
		'click .left': 'previousDemo',
		'click .right': 'nextDemo',
		'click .demo-import': 'importDemo',
		'click .plugins-install': 'installPlugin'
	},

	// The HTML template for the theme overlay
	html: demos.template( 'demo-single' ),

	render: function() {
		var data = this.model.toJSON();
		this.$el.html( this.html( data ) );
		// Renders active theme styles
		this.activeDemo();
		// Set up navigation events
		this.navigation();
		// Checks screenshot size
		this.screenshotCheck( this.$el );
		// Contain "tabbing" inside the overlay
		this.containFocus( this.$el );
	},

	// Adds a class to the currently active theme
	// and to the overlay in detailed view mode
	activeDemo: function() {
		// Check the model has the active property
		this.$el.toggleClass( 'active', this.model.get( 'active' ) );
	},

	// Set initial focus and constrain tabbing within the theme browser modal.
	containFocus: function( $el ) {

		// Set initial focus on the primary action control.
		_.delay( function() {
			$( '.theme-wrap a.button-primary:visible' ).focus();
		}, 100 );

		// Constrain tabbing within the modal.
		$el.on( 'keydown.wp-themes', function( event ) {
			var $firstFocusable = $el.find( '.theme-header button:not(.disabled)' ).first(),
				$lastFocusable = $el.find( '.theme-actions a:visible' ).last();

			// Check for the Tab key.
			if ( 9 === event.which ) {
				if ( $firstFocusable[0] === event.target && event.shiftKey ) {
					$lastFocusable.focus();
					event.preventDefault();
				} else if ( $lastFocusable[0] === event.target && ! event.shiftKey ) {
					$firstFocusable.focus();
					event.preventDefault();
				}
			}
		});
	},

	// Single demo overlay screen
	// It's shown when clicking a demo
	collapse: function( event ) {
		var self = this,
			scroll;

		event = event || window.event;

		// Prevent collapsing detailed view when there is only one demo available
		if ( demos.data.demos.length === 1 ) {
			return;
		}

		// Detect if the click is inside the overlay
		// and don't close it unless the target was
		// the div.back button
		if ( $( event.target ).is( '.theme-backdrop' ) || $( event.target ).is( '.close' ) || event.keyCode === 27 ) {

			// Add a temporary closing class while overlay fades out
			$( 'body' ).addClass( 'closing-overlay' );

			// With a quick fade out animation
			this.$el.fadeOut( 130, function() {
				// Clicking outside the modal box closes the overlay
				$( 'body' ).removeClass( 'closing-overlay' );
				// Handle event cleanup
				self.closeOverlay();

				// Get scroll position to avoid jumping to the top
				scroll = document.body.scrollTop;

				// Clean the url structure
				demos.router.navigate( demos.router.baseUrl( '' ) );

				// Restore scroll position
				document.body.scrollTop = scroll;

				// Return focus to the demo div
				if ( demos.focusedDemo ) {
					demos.focusedDemo.focus();
				}
			});
		}
	},

	// Handles .disabled classes for next/previous buttons
	navigation: function() {

		// Disable Left/Right when at the start or end of the collection
		if ( this.model.cid === this.model.collection.at(0).cid ) {
			this.$el.find( '.left' )
				.addClass( 'disabled' )
				.prop( 'disabled', true );
		}
		if ( this.model.cid === this.model.collection.at( this.model.collection.length - 1 ).cid ) {
			this.$el.find( '.right' )
				.addClass( 'disabled' )
				.prop( 'disabled', true );
		}
	},

	// Performs the actions to effectively close
	// the demo details overlay
	closeOverlay: function() {
		$( 'body' ).removeClass( 'modal-open' );
		this.remove();
		this.unbind();
		this.trigger( 'demo:collapse' );
	},

	importDemo: function( event ) {
		var _this = this,
			$target = $( event.target );
		event.preventDefault();

		if ( $target.hasClass( 'disabled' ) ) {
			return;
		}

		// Confirmation dialog for importing a demo.
		if ( ! window.confirm( wp.demos.data.settings.confirmImport ) ) {
			return;
		}

		$( document ).on( 'wp-demo-import-success', function( event, response ) {
			if ( _this.model.get( 'id' ) === response.slug ) {
				_this.model.set( { 'imported': true } );
			}
		} );

		// Handle a demo queue job.
		$( document ).on( 'wp-updates-queue-job', function( event, job ) {
			if ( 'import-demo' === job.action ) {
				wp.updates.importDemo( job.data );
			}
		} );

		wp.updates.importDemo( {
			slug: $target.data( 'slug' )
		} );
	},

	installPlugin: function( event ) {
		var itemsSelected = $( document ).find( 'input[name="required[]"], input[name="checked[]"]:checked' ),
			$target       = $( event.target ),
			success       = 0,
			error         = 0,
			errorMessages = [];

		event.preventDefault();

		if ( $target.hasClass( 'disabled' ) || $target.hasClass( 'updating-message' ) ) {
			return;
		}

		// Remove previous error messages, if any.
		$( '.theme-info .update-message' ).remove();

		// Bail if there were no items selected.
		if ( ! itemsSelected.length ) {
			event.preventDefault();
			$( '.theme-about' ).animate( { scrollTop: 0 } );
			$( '.theme-info .plugins-info' ).after( wp.updates.adminNotice( {
				id:        'no-items-selected',
				className: 'update-message notice-error notice-alt',
				message:   wp.updates.l10n.noItemsSelected
			} ) );
		}

		wp.updates.maybeRequestFilesystemCredentials( event );

		// Confirmation dialog for installing bulk plugins.
		if ( ! window.confirm( wp.demos.data.settings.confirmInstall ) ) {
			return;
		}

		// Un-check the bulk checkboxes.
		$( document ).find( '.manage-column [type="checkbox"]' ).prop( 'checked', false );

		$( document ).trigger( 'wp-plugin-bulk-install', itemsSelected );

		// Find all the checkboxes which have been checked.
		itemsSelected.each( function( index, element ) {
			var $checkbox = $( element ),
				$itemRow  = $checkbox.parents( 'tr' );

			// Only add install-able items to the update queue.
			if ( ! $itemRow.hasClass( 'install' ) || $itemRow.find( 'notice-error' ).length ) {

				// Un-check the box.
				$checkbox.filter( ':not(:disabled)' ).prop( 'checked', false );
				return;
			} else {
				$target
					.addClass( 'updating-message' )
					.text( wp.updates.l10n.installing );

				wp.a11y.speak( wp.updates.l10n.installingMsg, 'polite' );
			}

			// Add it to the queue.
			wp.updates.queue.push( {
				action: 'install-plugin',
				data:   {
					slug: $itemRow.data( 'slug' )
				}
			} );
		} );

		// Display bulk notification for install of plugin.
		$( document ).on( 'wp-plugin-bulk-install-success wp-plugin-bulk-install-error', function( event, response ) {
			var $itemRow = $( '[data-slug="' + response.slug + '"]' ),
				$bulkActionNotice, itemName;

			if ( 'wp-' + response.install + '-bulk-install-success' === event.type ) {
				success++;
			} else {
				itemName = response.pluginName ? response.pluginName : $itemRow.find( '.plugin-name' ).text();

				error++;
				errorMessages.push( itemName + ': ' + response.errorMessage );
			}

			$itemRow.find( 'input[name="checked[]"]:checked' ).filter( ':not(:disabled)' ).prop( 'checked', false );

			wp.updates.adminNotice = wp.template( 'wp-bulk-installs-admin-notice' );

			// Remove previous error messages, if any.
			$( '.theme-info .bulk-action-notice' ).remove();

			$( '.theme-info .plugins-info' ).after( wp.updates.adminNotice( {
				id:            'bulk-action-notice',
				className:     'bulk-action-notice notice-alt',
				successes:     success,
				errors:        error,
				errorMessages: errorMessages,
				type:          response.install
			} ) );

			$bulkActionNotice = $( '#bulk-action-notice' ).on( 'click', 'button', function() {
				// $( this ) is the clicked button, no need to get it again.
				$( this )
					.toggleClass( 'bulk-action-errors-collapsed' )
					.attr( 'aria-expanded', ! $( this ).hasClass( 'bulk-action-errors-collapsed' ) );
				// Show the errors list.
				$bulkActionNotice.find( '.bulk-action-errors' ).toggleClass( 'hidden' );
			} );

			if ( ! wp.updates.queue.length ) {
				if ( error > 0 ) {
					$target
						.removeClass( 'updating-message' ).addClass( 'disabled' )
						.text( wp.updates.l10n.installFailedShort );

					wp.a11y.speak( wp.updates.l10n.installedMsg, 'polite' );

					$( '.theme-about' ).animate( { scrollTop: 0 } );
				} else {
					$target
						.removeClass( 'updating-message' ).addClass( 'disabled' )
						.text( wp.updates.l10n.pluginInstalled );

					wp.a11y.speak( wp.updates.l10n.installedMsg, 'polite' );

					$( '.plugins-activate' ).removeAttr( 'disabled' );
				}
			}
		} );

		// Reset admin notice template after #bulk-action-notice was added.
		$( document ).on( 'wp-updates-notice-added', function() {
			wp.updates.adminNotice = wp.template( 'wp-updates-admin-notice' );
		} );

		// Check the queue, now that the event handlers have been added.
		wp.updates.queueChecker();
	},

	deleteDemo: function( event ) {
		var _this = this,
			_collection = this.model.collection,
			_demos = demos;
		event.preventDefault();

		// Confirmation dialog for deleting a demo.
		if ( ! window.confirm( wp.demos.data.settings.confirmDelete ) ) {
			return;
		}

		wp.updates.maybeRequestFilesystemCredentials( event );

		$( document ).one( 'wp-demo-delete-success', function( event, response ) {
			_this.$el.find( '.close' ).trigger( 'click' );
			$( '[data-slug="' + response.slug + '"' ).css( { backgroundColor:'#faafaa' } ).fadeOut( 350, function() {
				$( this ).remove();
				_demos.data.demos = _.without( _demos.data.demos, _.findWhere( _demos.data.demos, { id: response.slug } ) );

				$( '.wp-filter-search' ).val( '' );
				_collection.doSearch( '' );
				_collection.remove( _this.model );
				_collection.trigger( 'demos:update' );
			} );
		} );

		wp.updates.deleteDemo( {
			slug: this.model.get( 'id' )
		} );
	},

	nextDemo: function() {
		var self = this;
		self.trigger( 'demo:next', self.model.cid );
		return false;
	},

	previousDemo: function() {
		var self = this;
		self.trigger( 'demo:previous', self.model.cid );
		return false;
	},

	// Checks if the theme screenshot is the old 300px width version
	// and adds a corresponding class if it's true
	screenshotCheck: function( el ) {
		var screenshot, image;

		screenshot = el.find( '.screenshot img' );
		image = new Image();
		image.src = screenshot.attr( 'src' );

		// Width check
		if ( image.width && image.width <= 300 ) {
			el.addClass( 'small-screenshot' );
		}
	}
});

// Controls the rendering of div.themes,
// a wrapper that will hold all the theme elements
demos.view.Demos = wp.Backbone.View.extend({

	className: 'themes wp-clearfix',
	$overlay: $( 'div.theme-overlay' ),

	// Number to keep track of scroll position
	// while in theme-overlay mode
	index: 0,

	// The demo count element
	count: $( '.wrap .demo-count' ),

	// The live demos count
	liveDemoCount: 0,

	initialize: function( options ) {
		var self = this;

		// Set up parent
		this.parent = options.parent;

		// Move the imported demo to the beginning of the collection
		self.importedDemo();

		// Set current view to [grid]
		this.setView( 'grid' );

		// When the collection is updated by user input...
		this.listenTo( self.collection, 'demos:update', function() {
			self.parent.page = 0;
			self.importedDemo();
			self.render( this );
			self.parent.initTipTip();
		} );

		// Update demo count to full result set when available.
		this.listenTo( self.collection, 'query:success', function( count ) {
			if ( _.isNumber( count ) ) {
				self.count.text( count );
				self.announceSearchResults( count );
			} else {
				self.count.text( self.collection.length );
				self.announceSearchResults( self.collection.length );
			}
		});

		this.listenTo( self.collection, 'query:empty', function() {
			$( 'body' ).addClass( 'no-results' );
		});

		this.listenTo( this.parent, 'demo:scroll', function() {
			self.renderDemos( self.parent.page );
		});

		this.listenTo( this.parent, 'demo:close', function() {
			if ( self.overlay ) {
				self.overlay.closeOverlay();
			}
		} );

		// Bind keyboard events.
		$( 'body' ).on( 'keyup', function( event ) {
			if ( ! self.overlay ) {
				return;
			}

			// Bail if the filesystem credentials dialog is shown.
			if ( $( '#request-filesystem-credentials-dialog' ).is( ':visible' ) ) {
				return;
			}

			// Pressing the right arrow key fires a demo:next event
			if ( event.keyCode === 39 ) {
				self.overlay.nextDemo();
			}

			// Pressing the left arrow key fires a demo:previous event
			if ( event.keyCode === 37 ) {
				self.overlay.previousDemo();
			}

			// Pressing the escape key fires a demo:collapse event
			if ( event.keyCode === 27 ) {
				self.overlay.collapse( event );
			}
		});
	},

	// Manages rendering of demo pages
	// and keeping demo count in sync
	render: function() {
		// Clear the DOM, please
		this.$el.empty();

		// If the user doesn't have switch capabilities
		// or there is only one demo in the collection
		// render the detailed view of the active demo
		if ( ! demos.isPreview && demos.data.demos.length === 1 ) {

			// Constructs the view
			this.singleDemo = new demos.view.Details({
				model: this.collection.models[0]
			});

			// Render and apply a 'single-theme' class to our container
			this.singleDemo.render();
			this.$el.addClass( 'single-theme' );
			this.$el.append( this.singleDemo.el );
		}

		// Generate the demos
		// Using page instance
		// While checking the collection has items
		if ( this.options.collection.size() > 0 ) {
			this.renderDemos( this.parent.page );
		}

		// Display a live demo count for the collection
		this.liveDemoCount = this.collection.count ? this.collection.count : this.collection.length;
		this.count.text( this.liveDemoCount );
	},

	// Iterates through each instance of the collection
	// and renders each theme module
	renderDemos: function( page ) {
		var self = this;

		self.instance = self.collection.paginate( page );

		// If we have no more demos bail
		if ( self.instance.size() === 0 ) {
			// Fire a no-more-demos event.
			this.parent.trigger( 'demo:end' );
			return;
		}

		// Loop through the demos and setup each demo view
		self.instance.each( function( demo ) {
			self.demo = new demos.view.Demo({
				model: demo,
				parent: self
			});

			// Render the views...
			self.demo.render();
			// and append them to div.themes
			self.$el.append( self.demo.el );

			// Binds to demo:expand to show the modal box
			// with the demo details
			self.listenTo( self.demo, 'demo:expand', self.expand, self );
		});

		// 'Add new demo' element shown at the end of the grid
		if ( ! demos.isPreview && demos.isInstall && demos.data.settings.canInstall ) {
			this.$el.append( '<div class="theme add-new-theme"><a href="' + demos.data.settings.installURI + '"><div class="theme-screenshot"><span></span></div><h2 class="theme-name">' + l10n.addNew + '</h2></a></div>' );
		}

		this.parent.page++;
	},

	// Grabs imported demo and puts it at the beginning of the collection
	importedDemo: function() {
		var self = this,
			current;

		current = self.collection.findWhere({ active: true });

		// Move the imported demo to the beginning of the collection
		if ( current ) {
			self.collection.remove( current );
			self.collection.add( current, { at:0 } );
		}
	},

	// Sets current view
	setView: function( view ) {
		return view;
	},

	// Renders the overlay with the DemoDetails view
	// Uses the current model data
	expand: function( id ) {
		var self = this, $card, $modal;

		// Set the current demo model
		this.model = self.collection.get( id );

		// Trigger a route update for the current model
		demos.router.navigate( demos.router.baseUrl( demos.router.demoPath + this.model.id ) );

		// Sets this.view to 'detail'
		this.setView( 'detail' );
		$( 'body' ).addClass( 'modal-open' );

		// Set up the demo details view
		this.overlay = new demos.view.Details({
			model: self.model
		});

		this.overlay.render();

		if ( this.model.get( 'hasUpdate' ) ) {
			$card  = $( '[data-slug="' + this.model.id + '"]' );
			$modal = $( this.overlay.el );

			if ( $card.find( '.updating-message' ).length ) {
				$modal.find( '.notice-warning h3' ).remove();
				$modal.find( '.notice-warning' )
					.removeClass( 'notice-large' )
					.addClass( 'updating-message' )
					.find( 'p' ).text( wp.updates.l10n.updating );
			} else if ( $card.find( '.notice-error' ).length ) {
				$modal.find( '.notice-warning' ).remove();
			}
		}

		this.$overlay.html( this.overlay.el );

		// Bind to demo:next and demo:previous
		// triggered by the arrow keys
		//
		// Keep track of the current model so we
		// can infer an index position
		this.listenTo( this.overlay, 'demo:next', function() {
			// Renders the next demo on the overlay
			self.next( [ self.model.cid ] );

		})
		.listenTo( this.overlay, 'demo:previous', function() {
			// Renders the previous demo on the overlay
			self.previous( [ self.model.cid ] );
		});
	},

	// This method renders the next demo on the overlay modal
	// based on the current position in the collection
	// @params [model cid]
	next: function( args ) {
		var self = this,
			model, nextModel;

		// Get the current demo
		model = self.collection.get( args[0] );
		// Find the next model within the collection
		nextModel = self.collection.at( self.collection.indexOf( model ) + 1 );

		// Sanity check which also serves as a boundary test
		if ( nextModel !== undefined ) {

			// We have a new demo...
			// Close the overlay
			this.overlay.closeOverlay();

			// Trigger a route update for the current model
			self.demo.trigger( 'demo:expand', nextModel.cid );
		}
	},

	// This method renders the previous demo on the overlay modal
	// based on the current position in the collection
	// @params [model cid]
	previous: function( args ) {
		var self = this,
			model, previousModel;

		// Get the current demo
		model = self.collection.get( args[0] );
		// Find the previous model within the collection
		previousModel = self.collection.at( self.collection.indexOf( model ) - 1 );

		if ( previousModel !== undefined ) {

			// We have a new demo...
			// Close the overlay
			this.overlay.closeOverlay();

			// Trigger a route update for the current model
			self.demo.trigger( 'demo:expand', previousModel.cid );
		}
	},

	// Dispatch audible search results feedback message
	announceSearchResults: function( count ) {
		if ( 0 === count ) {
			wp.a11y.speak( l10n.noDemosFound );
		} else {
			wp.a11y.speak( l10n.demosFound.replace( '%d', count ) );
		}
	}
});

// Search input view controller.
demos.view.Search = wp.Backbone.View.extend({

	tagName: 'input',
	className: 'wp-filter-search',
	id: 'wp-filter-search-input',
	searching: false,

	attributes: {
		placeholder: l10n.searchPlaceholder,
		type: 'search',
		'aria-describedby': 'live-search-desc'
	},

	events: {
		'input': 'search',
		'keyup': 'search',
		'blur': 'pushState'
	},

	initialize: function( options ) {

		this.parent = options.parent;

		this.listenTo( this.parent, 'demo:close', function() {
			this.searching = false;
		} );
	},

	search: function( event ) {
		// Clear on escape.
		if ( event.type === 'keyup' && event.which === 27 ) {
			event.target.value = '';
		}

		/**
		 * Since doSearch is debounced, it will only run when user input comes to a rest
		 */
		this.doSearch( event );
	},

	// Runs a search on the demo collection.
	doSearch: _.debounce( function( event ) {
		var options = {};

		this.collection.doSearch( event.target.value );

		// if search is initiated and key is not return
		if ( this.searching && event.which !== 13 ) {
			options.replace = true;
		} else {
			this.searching = true;
		}

		// Update the URL hash
		if ( event.target.value ) {
			demos.router.navigate( demos.router.baseUrl( demos.router.searchPath + event.target.value ), options );
		} else {
			demos.router.navigate( demos.router.baseUrl( '' ) );
		}
	}, 500 ),

	pushState: function( event ) {
		var url = demos.router.baseUrl( '' );

		if ( event.target.value ) {
			url = demos.router.baseUrl( demos.router.searchPath + event.target.value );
		}

		this.searching = false;
		demos.router.navigate( url );
	}
});

// Sets up the routes events for relevant url queries
// Listens to [demo] and [search] params
demos.Router = Backbone.Router.extend({

	routes: {
		'themes.php?page=demo-importer&demo=:slug': 'demo',
		'themes.php?page=demo-importer&search=:query': 'search',
		'themes.php?page=demo-importer&s=:query': 'search',
		'themes.php?page=demo-importer': 'demos',
		'': 'demos'
	},

	baseUrl: function( url ) {
		return 'themes.php?page=demo-importer' + url;
	},

	demoPath: '&demo=',
	searchPath: '&search=',

	search: function( query ) {
		$( '.wp-filter-search' ).val( query );
	},

	demos: function() {
		$( '.wp-filter-search' ).val( '' );
	},

	navigate: function() {
		if ( Backbone.history._hasPushState ) {
			Backbone.Router.prototype.navigate.apply( this, arguments );
		}
	}
});

// Execute and setup the application
demos.Run = {
	init: function() {
		// Initializes the blog's demo library view
		// Create a new collection with data
		this.demos = new demos.Collection( demos.data.demos );

		// Set up the view
		this.view = new demos.view.Appearance({
			collection: this.demos
		});

		this.render();
	},

	render: function() {

		// Render results
		this.view.render();
		this.view.initTipTip();
		this.routes();

		Backbone.history.start({
			root: demos.data.settings.adminUrl,
			pushState: true,
			hashChange: false
		});
	},

	routes: function() {
		var self = this;
		// Bind to our global thx object
		// so that the object is available to sub-views
		demos.router = new demos.Router();

		// Handles demo details route event
		demos.router.on( 'route:demo', function( slug ) {
			self.view.view.expand( slug );
		});

		demos.router.on( 'route:demos', function() {
			self.demos.doSearch( '' );
			self.view.trigger( 'demo:close' );
		});

		// Handles search route event
		demos.router.on( 'route:search', function() {
			$( '.wp-filter-search' ).trigger( 'keyup' );
		});

		this.extraRoutes();
	},

	extraRoutes: function() {
		return false;
	}
};

demos.view.Installer = demos.view.Appearance.extend({

	el: '#wpbody-content .wrap',

	// Initial render method
	render: function() {
		this.search();
		this.uploader();

		// Setup the main demo view
		// with the current demo collection
		this.view = new demos.view.Demos({
			collection: this.collection,
			parent: this
		});

		// Render and append
		this.$el.find( '.themes' ).remove();
		this.view.render();
		this.$el.find( '.theme-browser' ).append( this.view.el ).addClass( 'rendered' );
	},

	/*
	 * When users press the "Upload Theme" button, show the upload form in place.
	 */
	uploader: function() {
		var uploadViewToggle = $( '.upload-view-toggle' ),
			$body = $( document.body );

		uploadViewToggle.on( 'click', function() {
			// Toggle the upload view.
			$body.toggleClass( 'show-upload-view' );
			// Toggle the `aria-expanded` button attribute.
			uploadViewToggle.attr( 'aria-expanded', $body.hasClass( 'show-upload-view' ) );
		});
	},

	clearSearch: function() {
		$( '#wp-filter-search-input').val( '' );
	}
});

demos.InstallerRouter = Backbone.Router.extend({

	routes: {
		'themes.php?page=demo-importer&browse=uploads&demo=:slug': 'demo',
		'themes.php?page=demo-importer&browse=:sort&search=:query': 'search',
		'themes.php?page=demo-importer&browse=:sort&s=:query': 'search',
		'themes.php?page=demo-importer&browse=welcome': 'sort',
		'themes.php?page=demo-importer&browse=:sort': 'demos',
		'themes.php?page=demo-importer': 'sort'
	},

	browse: function() {
		return demos.isPreview ? 'preview' : 'uploads';
	},

	baseUrl: function( url ) {
		return 'themes.php?page=demo-importer&browse=' + this.browse() + url;
	},

	demoPath: '&demo=',
	searchPath: '&search=',

	search: function( sort, query ) {
		$( '.wp-filter-search' ).val( query );
	},

	demos: function() {
		$( '.wp-filter-search' ).val( '' );
	},

	navigate: function() {
		if ( Backbone.history._hasPushState ) {
			Backbone.Router.prototype.navigate.apply( this, arguments );
		}
	}
});

demos.RunInstaller = {

	init: function() {
		// Initializes the blog's demo library view
		// Create a new collection with data
		this.demos = new demos.Collection( demos.data.demos );

		// Set up the view
		this.view = new demos.view.Installer({
			collection: this.demos
		});

		this.render();
	},

	render: function() {

		// Render results
		this.view.render();
		this.view.initTipTip();
		this.routes();

		Backbone.history.start({
			root: demos.data.settings.adminUrl,
			pushState: true,
			hashChange: false
		});
	},

	routes: function() {
		var self = this;
		// Bind to our global thx object
		// so that the object is available to sub-views
		demos.router = new demos.InstallerRouter();

		// Handles demo details route event
		demos.router.on( 'route:demo', function( slug ) {
			self.view.view.expand( slug );
		});

		demos.router.on( 'route:demos', function() {
			self.demos.doSearch( '' );
			self.view.trigger( 'demo:close' );
		});

		// Handles sorting / browsing routes
		// Also handles the root URL triggering a sort request
		// for `welcome`, the default view
		demos.router.on( 'route:sort', function( sort ) {
			if ( ! sort || 'welcome' === sort ) {
				$( '.wp-filter-search' ).hide();
			}
		});

		// The `search` route event. The router populates the input field.
		demos.router.on( 'route:search', function() {
			$( '.wp-filter-search' ).focus().trigger( 'keyup' );
		});

		this.extraRoutes();
	},

	extraRoutes: function() {
		return false;
	}
};

// Ready...
$( document ).ready( function() {
	if ( demos.isInstall ) {
		demos.RunInstaller.init();
	} else {
		demos.Run.init();
	}

	// Rating footer.
	$( '.themegrill-demo-importer-rating-link' ).on( 'click', function() {
		var $this_el = $( this );

		$.post( demos.data.settings.ajaxUrl, {
			action: 'footer-text-rated'
		});

		$this_el.parent().text( $this_el.data( 'rated' ) );
	} );

	// Confirm WordPress reset wizard.
	$( '.themegrill-reset-wordpress' ).on( 'click', function() {
		return window.confirm( demos.data.settings.confirmReset );
	});

	// Load videos when help button is clicked.
	$( '#contextual-help-link' ).on( 'click', function() {
		var frame = $( '#tab-panel-themegrill_demo_importer_guided_tour_tab iframe' );

		frame.attr( 'src', frame.data( 'src' ) );
	});

	// Make disabled checkbox always checked through data-checked.
	$( document.body ).on( 'click', 'thead .check-column :checkbox', function( event ) {
		var $this = $( this ),
			$table = $this.closest( 'table' ),
			controlChecked = $this.prop( 'checked' ),
			toggle = event.shiftKey || $this.data( 'wp-toggle' );

		$table.children( 'tbody' ).filter( ':visible' )
			.children().children( '.check-column' ).find( ':checkbox' )
			.prop( 'checked', function() {
				if ( $( this ).is( ':hidden,:disabled' ) ) {
					return $( this ).data( 'checked' ) ? true : false;
				}

				if ( toggle ) {
					return ! $( this ).prop( 'checked' );
				} else if ( controlChecked ) {
					return true;
				}

				return false;
			});
	});
});

})( jQuery );
