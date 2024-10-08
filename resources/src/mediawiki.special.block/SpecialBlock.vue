<template>
	<cdx-field
		class="mw-block-fieldset"
		:is-fieldset="true"
		:disabled="formDisabled"
	>
		<div ref="messagesContainer" class="mw-block-messages">
			<cdx-message
				v-if="success"
				type="success"
				:allow-user-dismiss="true"
				class="mw-block-success"
			>
				<p><strong>{{ $i18n( 'blockipsuccesssub' ) }}</strong></p>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<p v-html="$i18n( 'block-success', targetUser ).parse()"></p>
			</cdx-message>
			<cdx-message
				v-for="( formError, index ) in formErrors"
				:key="index"
				type="error"
				class="mw-block-error"
				inline
			>
				{{ formError }}
			</cdx-message>
		</div>
		<user-lookup
			v-model="targetUser"
			@input="alreadyBlocked = false"
			:form-submitted="formSubmitted"
		></user-lookup>
		<target-active-blocks
			v-if="blockEnableMultiblocks"
			:target-user="targetUser"
		></target-active-blocks>
		<target-block-log
			:target-user="targetUser"
		></target-block-log>
		<block-type-field
			v-model="blockPartialOptionsSelected"
			v-model:block-type-value="blockType"
			:partial-block-options="blockPartialOptions"
		></block-type-field>
		<expiry-field
			v-model="expiry"
			:form-submitted="formSubmitted"
		></expiry-field>
		<reason-field
			v-model:selected="reasonSelected"
			v-model:other="reasonOther"
		></reason-field>
		<block-details-field
			v-model="blockDetailsSelected"
			:checkboxes="blockDetailsOptions"
			:label="$i18n( 'block-details' ).text()"
			:description="$i18n( 'block-details-description' ).text()"
		></block-details-field>
		<block-details-field
			v-model="blockAdditionalDetailsSelected"
			:checkboxes="additionalDetailsOptions"
			:label="$i18n( 'block-options' ).text()"
			:description="$i18n( 'block-options-description' ).text()"
		></block-details-field>
		<hr class="mw-block-hr">
		<cdx-button
			action="destructive"
			weight="primary"
			class="mw-block-submit"
			@click="handleSubmit"
		>
			{{ submitButtonMessage }}
		</cdx-button>
	</cdx-field>
</template>

<script>
const { computed, defineComponent, nextTick, ref, Ref } = require( 'vue' );
const { CdxButton, CdxField, CdxMessage } = require( '@wikimedia/codex' );
const UserLookup = require( './components/UserLookup.vue' );
const TargetActiveBlocks = require( './components/TargetActiveBlocks.vue' );
const TargetBlockLog = require( './components/TargetBlockLog.vue' );
const BlockTypeField = require( './components/BlockTypeField.vue' );
const ExpiryField = require( './components/ExpiryField.vue' );
const ReasonField = require( './components/ReasonField.vue' );
const BlockDetailsField = require( './components/BlockDetailsOptions.vue' );
const api = new mw.Api();

module.exports = exports = defineComponent( {
	name: 'SpecialBlock',
	components: {
		UserLookup,
		TargetActiveBlocks,
		TargetBlockLog,
		BlockTypeField,
		ExpiryField,
		ReasonField,
		BlockDetailsField,
		CdxButton,
		CdxField,
		CdxMessage
	},
	setup() {
		const targetUser = ref( mw.config.get( 'blockTargetUser', '' ) );
		const alreadyBlocked = ref( mw.config.get( 'blockAlreadyBlocked' ) );
		const blockEnableMultiblocks = mw.config.get( 'blockEnableMultiblocks' ) || false;
		const success = ref( false );
		/**
		 * Whether the form has been submitted. This is used to only show error states
		 * after the user has attempted to submit the form.
		 *
		 * @type {Ref<boolean>}
		 */
		const formSubmitted = ref( false );
		const formDisabled = ref( false );
		const messagesContainer = ref();
		const formErrors = ref( mw.config.get( 'blockPreErrors' ) );
		// eslint-disable-next-line arrow-body-style
		const submitButtonMessage = computed( () => {
			return mw.message( alreadyBlocked.value ? 'ipb-change-block' : 'ipbsubmit' ).text();
		} );
		const blockType = ref( 'sitewide' );
		const expiry = ref( {} );
		const blockPartialOptions = mw.config.get( 'partialBlockActionOptions' ) ?
			Object.keys( mw.config.get( 'partialBlockActionOptions' ) ).map(
				// Messages that can be used here:
				// * ipb-action-upload
				// * ipb-action-move
				// * ipb-action-create
				( key ) => Object( { label: mw.message( key ).text(), value: key } ) ) :
			[];
		const blockPartialOptionsSelected = ref( [ 'ipb-action-create' ] );
		const reasonSelected = ref( 'other' );
		const reasonOther = ref( '' );
		const blockAllowsUTEdit = mw.config.get( 'blockAllowsUTEdit' ) || false;
		const blockEmailBan = mw.config.get( 'blockAllowsEmailBan' ) || false;
		const blockAutoblockExpiry = mw.config.get( 'blockAutoblockExpiry' );
		const blockHideUser = mw.config.get( 'blockHideUser' ) || false;
		const blockDetailsSelected = ref( mw.config.get( 'blockDetailsPreset' ) );
		const blockDetailsOptions = [
			{
				label: mw.message( 'ipbcreateaccount' ),
				value: 'wpCreateAccount'
			}
		];

		if ( blockEmailBan ) {
			blockDetailsOptions.push( {
				label: mw.message( 'ipbemailban' ),
				value: 'wpDisableEmail'
			} );
		}

		if ( blockAllowsUTEdit ) {
			blockDetailsOptions.push( {
				label: mw.message( 'ipb-disableusertalk' ),
				value: 'wpDisableUTEdit'
			} );
		}

		const blockAdditionalDetailsSelected = ref( mw.config.get( 'blockAdditionalDetailsPreset' ) );
		const additionalDetailsOptions = [ {
			label: mw.message( 'ipbenableautoblock', blockAutoblockExpiry ),
			value: 'wpAutoBlock',
			disabled: false
		} ];

		if ( blockHideUser ) {
			additionalDetailsOptions.push( {
				label: mw.message( 'ipbhidename' ),
				value: 'wpHideName'
			} );
		}

		additionalDetailsOptions.push( {
			label: mw.message( 'ipbwatchuser' ),
			value: 'wpWatch',
			disabled: false
		} );

		additionalDetailsOptions.push( {
			label: mw.message( 'ipb-hardblock' ),
			value: 'wpHardBlock',
			disabled: false
		} );

		/**
		 * Handle form submission. If the form is invalid, show the browser's
		 * validation messages along with any custom validation messages, and
		 * scroll to the first error message to ensure the user sees it.
		 *
		 * If the form is valid, send the block request to the server,
		 * add the success message and scroll to it.
		 *
		 * @param {Event} event
		 */
		function handleSubmit( event ) {
			event.preventDefault();
			formSubmitted.value = true;

			// checkValidity() executes browser form validation, which triggers automatic
			// validation states on applicable components (e.g. fields with `required` attr).
			if ( event.target.form.checkValidity() && expiry.value.value ) {
				block();
			} else {
				// nextTick() needed to ensure error messages are rendered before scrolling.
				nextTick( () => {
					// Currently, only the expiry field has custom validations.
					// Scrolling to `cdx-message--error` is merely future-proofing to
					// ensure the user sees the error message, wherever it may be.
					// Actual validation logic should live in the respective component,
					// and only apply after the form has been submitted via a `formSubmitted` prop.
					document.querySelector( '.cdx-message--error' )
						.scrollIntoView( { behavior: 'smooth' } );
				} );
			}
		}

		/**
		 * Send block.
		 *
		 * @return {jQuery.Promise}
		 */
		function block() {
			formDisabled.value = true;

			const params = {
				action: 'block',
				reblock: alreadyBlocked.value ? 1 : 0,
				format: 'json',
				user: targetUser.value,
				// Remove browser-specific milliseconds for consistency.
				expiry: expiry.value.value.replace( /\.000$/, '' ),
				// Localize errors
				uselang: mw.config.get( 'wgUserLanguage' ),
				errorlang: mw.config.get( 'wgUserLanguage' ),
				errorsuselocal: true
			};

			// Reason selected concatenated with 'Other' field
			if ( reasonSelected.value === 'other' ) {
				params.reason = reasonOther.value;
			} else {
				params.reason = reasonSelected.value + (
					reasonOther.value ? mw.msg( 'colon-separator' ) + reasonOther.value : ''
				);
			}

			if ( blockType.value === 'partial' ) {
				const actionRestrictions = [];
				params.partial = 1;
				if ( blockPartialOptionsSelected.value.indexOf( 'ipb-action-upload' ) !== -1 ) {
					actionRestrictions.push( 'upload' );
				}
				if ( blockPartialOptionsSelected.value.indexOf( 'ipb-action-move' ) !== -1 ) {
					actionRestrictions.push( 'move' );
				}
				if ( blockPartialOptionsSelected.value.indexOf( 'ipb-action-create' ) !== -1 ) {
					actionRestrictions.push( 'create' );
				}
				params.actionRestrictions = actionRestrictions.join( '|' );
			}

			if ( blockDetailsSelected.value.indexOf( 'wpCreateAccount' ) !== -1 ) {
				params.nocreate = 1;
			}

			if ( blockDetailsSelected.value.indexOf( 'wpDisableEmail' ) !== -1 ) {
				params.noemail = 1;
			}

			if ( blockDetailsSelected.value.indexOf( 'wpDisableUTEdit' ) !== -1 ) {
				params.allowusertalk = 1;
			}

			if ( blockAdditionalDetailsSelected.value.indexOf( 'wpAutoBlock' ) !== -1 ) {
				params.autoblock = 1;
			}

			if ( blockAdditionalDetailsSelected.value.indexOf( 'wpHideName' ) !== -1 ) {
				params.hidename = 1;
			}

			if ( blockAdditionalDetailsSelected.value.indexOf( 'wpWatch' ) !== -1 ) {
				params.watchuser = 1;
			}

			if ( blockAdditionalDetailsSelected.value.indexOf( 'wpHardBlock' ) !== -1 ) {
				params.nocreate = 1;
			}

			// Clear messages.
			success.value = false;
			formErrors.value = [];

			return api.postWithEditToken( params )
				.done( () => {
					success.value = true;
				} )
				.fail( ( _code, errorObj ) => {
					success.value = false;
					formErrors.value = [ errorObj.error.info ];
				} )
				.always( () => {
					formDisabled.value = false;
					messagesContainer.value.scrollIntoView( { behavior: 'smooth' } );
				} );
		}

		return {
			formSubmitted,
			formDisabled,
			messagesContainer,
			formErrors,
			success,
			targetUser,
			blockType,
			expiry,
			alreadyBlocked,
			submitButtonMessage,
			handleSubmit,
			blockDetailsOptions,
			blockDetailsSelected,
			reasonOther,
			reasonSelected,
			additionalDetailsOptions,
			blockAdditionalDetailsSelected,
			blockPartialOptions,
			blockPartialOptionsSelected,
			blockEnableMultiblocks
		};
	}
} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

// HACK: The accordions within the form need to be full width.
.mw-htmlform-codex {
	max-width: unset;
}

// HACK: Set the max-width of the fields back to what they should be.
.cdx-field:not( .mw-block-fieldset ) {
	max-width: @size-4000;
}

.mw-block-hr {
	margin-top: @spacing-200;
}

.mw-block-submit.cdx-button {
	margin-top: @spacing-100;
}

.mw-block-error {
	margin-left: @spacing-75;
}
</style>
