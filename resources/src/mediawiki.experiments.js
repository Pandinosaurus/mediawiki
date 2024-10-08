/*!
 * @author Ori Livneh <ori@wikimedia.org>
 */
( function () {
	/**
	 * Provides an API for bucketing users in experiments.
	 *
	 * @namespace mw.experiments
	 * @singleton
	 */

	const CONTROL_BUCKET = 'control',
		MAX_INT32_UNSIGNED = 4294967295;

	/**
	 * An implementation of Jenkins' one-at-a-time hash.
	 *
	 * See <https://en.wikipedia.org/wiki/Jenkins_hash_function>.
	 *
	 * @private
	 * @param {string} string String to hash
	 * @return {number} The hash as a 32-bit unsigned integer
	 */
	function hashString( string ) {
		/* eslint-disable no-bitwise */
		let hash = 0,
			i = string.length;

		while ( i-- ) {
			hash += string.charCodeAt( i );
			hash += ( hash << 10 );
			hash ^= ( hash >> 6 );
		}
		hash += ( hash << 3 );
		hash ^= ( hash >> 11 );
		hash += ( hash << 15 );

		return hash >>> 0;
		/* eslint-enable no-bitwise */
	}

	mw.experiments = {

		/**
		 * Gets the bucket for the experiment given the token.
		 *
		 * The name of the experiment and the token are hashed. The hash is converted
		 * to a number which is then used to get a bucket.
		 *
		 * @example
		 * // The experiment has three buckets: control, A, and B. The user has a 50% chance of
		 * // being assigned to the control bucket, and a 25% chance of being assigned to either
		 * // the A or B bucket. If the experiment were disabled, then the user would always be
		 * // assigned to the control bucket.
		 * {
		 *   name: 'My first experiment',
		 *   enabled: true,
		 *   buckets: {
		 *     control: 0.5
		 *     A: 0.25,
		 *     B: 0.25
		 *   }
		 * }
		 *
		 * @param {Object} experiment
		 * @param {string} experiment.name The name of the experiment
		 * @param {boolean} experiment.enabled Whether or not the experiment is
		 *  enabled. If the experiment is disabled, then the user is always assigned
		 *  to the control bucket
		 * @param {Object} experiment.buckets A map of bucket name to probability
		 *  that the user will be assigned to that bucket
		 * @param {string} token A token that uniquely identifies the user for the
		 *  duration of the experiment
		 * @return {string|undefined} The bucket
		 */
		getBucket: function ( experiment, token ) {
			const buckets = experiment.buckets;
			let range = 0,
				acc = 0;

			if ( !experiment.enabled || !Object.keys( experiment.buckets ).length ) {
				return CONTROL_BUCKET;
			}

			for ( const key in buckets ) {
				range += buckets[ key ];
			}

			const hash = hashString( experiment.name + ':' + token );
			const max = ( hash / MAX_INT32_UNSIGNED ) * range;

			for ( const key in buckets ) {
				acc += buckets[ key ];

				if ( max <= acc ) {
					return key;
				}
			}
		}
	};

}() );
