<?php

namespace MediaWiki\DomainEvent;

/**
 * Objects implementing DomainEventSubscriber represent a collection of
 * related event listeners.
 *
 * @since 1.44
 * @stable to type
 * @note Extensions should not implement this interface directly but should
 *       extend EventIngressBase.
 */
interface DomainEventSubscriber {

	/**
	 * Registers listeners with the given $eventSource.
	 */
	public function registerListeners( DomainEventSource $eventSource ): void;

}
