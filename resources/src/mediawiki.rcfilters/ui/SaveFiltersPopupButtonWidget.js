/**
 * Save filters widget. This widget is displayed in the tag area
 * and allows the user to save the current state of the system
 * as a new saved filter query they can later load or set as
 * default.
 *
 * @class mw.rcfilters.ui.SaveFiltersPopupButtonWidget
 * @ignore
 * @extends OO.ui.PopupButtonWidget
 *
 * @param {mw.rcfilters.Controller} controller Controller
 * @param {mw.rcfilters.dm.SavedQueriesModel} model View model
 * @param {Object} [config] Configuration object
 */
const SaveFiltersPopupButtonWidget = function MwRcfiltersUiSaveFiltersPopupButtonWidget( controller, model, config ) {
	const $popupContent = $( '<div>' );

	config = config || {};

	this.controller = controller;
	this.model = model;

	// Parent
	SaveFiltersPopupButtonWidget.super.call( this, Object.assign( {
		framed: false,
		icon: 'bookmark',
		title: mw.msg( 'rcfilters-savedqueries-add-new-title' ),
		popup: {
			classes: [ 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup' ],
			padded: true,
			head: true,
			// Make the popup slightly wider to accommodate titles and labels
			// from languages that are longer than the original English ones.
			// See T217304
			width: 450,
			icon: 'bookmark',
			label: mw.msg( 'rcfilters-savedqueries-add-new-title' ),
			$content: $popupContent
		}
	}, config ) );

	this.input = new OO.ui.TextInputWidget( {
		placeholder: mw.msg( 'rcfilters-savedqueries-new-name-placeholder' )
	} );
	const layout = new OO.ui.FieldLayout( this.input, {
		label: mw.msg( 'rcfilters-savedqueries-new-name-label' ),
		align: 'top'
	} );

	this.setAsDefaultCheckbox = new OO.ui.CheckboxInputWidget();
	const checkBoxLayout = new OO.ui.FieldLayout( this.setAsDefaultCheckbox, {
		label: mw.msg( 'rcfilters-savedqueries-setdefault' ),
		align: 'inline'
	} );

	this.applyButton = new OO.ui.ButtonWidget( {
		label: mw.msg( 'rcfilters-savedqueries-apply-label' ),
		classes: [ 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup-buttons-apply' ],
		flags: [ 'primary', 'progressive' ]
	} );
	this.cancelButton = new OO.ui.ButtonWidget( {
		label: mw.msg( 'rcfilters-savedqueries-cancel-label' ),
		classes: [ 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup-buttons-cancel' ]
	} );

	$popupContent
		.append(
			$( '<div>' )
				.addClass( 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup-layout' )
				.append( layout.$element ),
			$( '<div>' )
				.addClass( 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup-options' )
				.append( checkBoxLayout.$element ),
			$( '<div>' )
				.addClass( 'mw-rcfilters-ui-saveFiltersPopupButtonWidget-popup-buttons' )
				.append(
					this.cancelButton.$element,
					this.applyButton.$element
				)
		);

	// Events
	this.popup.connect( this, {
		ready: 'onPopupReady'
	} );
	this.input.connect( this, {
		change: 'onInputChange',
		enter: 'onInputEnter'
	} );
	this.input.$input.on( {
		keyup: this.onInputKeyup.bind( this )
	} );
	this.setAsDefaultCheckbox.connect( this, { change: 'onSetAsDefaultChange' } );
	this.cancelButton.connect( this, { click: 'onCancelButtonClick' } );
	this.applyButton.connect( this, { click: 'onApplyButtonClick' } );

	// Initialize
	this.applyButton.setDisabled( !this.input.getValue() );
	this.$element
		.addClass( 'mw-rcfilters-ui-saveFiltersPopupButtonWidget' );
};

/* Initialization */
OO.inheritClass( SaveFiltersPopupButtonWidget, OO.ui.PopupButtonWidget );

/**
 * Respond to input enter event
 */
SaveFiltersPopupButtonWidget.prototype.onInputEnter = function () {
	this.apply();
};

/**
 * Respond to input change event
 *
 * @param {string} value Input value
 */
SaveFiltersPopupButtonWidget.prototype.onInputChange = function ( value ) {
	value = value.trim();

	this.applyButton.setDisabled( !value );
};

/**
 * Respond to input keyup event, this is the way to intercept 'escape' key
 *
 * @param {jQuery.Event} e Event data
 * @return {boolean|undefined} false
 */
SaveFiltersPopupButtonWidget.prototype.onInputKeyup = function ( e ) {
	if ( e.which === OO.ui.Keys.ESCAPE ) {
		this.popup.toggle( false );
		return false;
	}
};

/**
 * Respond to popup ready event
 */
SaveFiltersPopupButtonWidget.prototype.onPopupReady = function () {
	this.input.focus();
};

/**
 * Respond to "set as default" checkbox change
 *
 * @param {boolean} checked State of the checkbox
 */
SaveFiltersPopupButtonWidget.prototype.onSetAsDefaultChange = function ( checked ) {
	this.applyButton
		.setIcon( checked ? 'pushPin' : null );
};

/**
 * Respond to cancel button click event
 */
SaveFiltersPopupButtonWidget.prototype.onCancelButtonClick = function () {
	this.popup.toggle( false );
};

/**
 * Respond to apply button click event
 */
SaveFiltersPopupButtonWidget.prototype.onApplyButtonClick = function () {
	this.apply();
};

/**
 * Apply and add the new quick link
 */
SaveFiltersPopupButtonWidget.prototype.apply = function () {
	const label = this.input.getValue().trim();

	// This condition is more for double-checking, since the
	// apply button should be disabled if the label is empty
	if ( label ) {
		this.controller.saveCurrentQuery( label, this.setAsDefaultCheckbox.isSelected() );
		this.input.setValue( '' );
		this.setAsDefaultCheckbox.setSelected( false );
		this.popup.toggle( false );

		this.emit( 'saveCurrent' );
	}
};

module.exports = SaveFiltersPopupButtonWidget;
