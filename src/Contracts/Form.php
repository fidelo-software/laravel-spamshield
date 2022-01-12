<?php

namespace FideloSoftware\Spam\Contracts;

interface Form {

	/**
	 * Get unique form id to recognize instance in store
	 *
	 * @return string
	 */
	public function getUid(): string;

	/**
	 * Returns all values of the form
	 *
	 * return [
	 * 		'fieldname' => 'value'
	 * ]
	 *
	 * @param array $fieldNames
	 * @return array
	 */
	public function getFieldValues(array $fieldNames = []): array;

}
