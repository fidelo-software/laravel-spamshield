<?php

namespace FideloSoftware\Spam\Strategies;

use FideloSoftware\Spam\Contracts\Form;
use Illuminate\Http\Request;

/**
 * Value-Blacklist
 *
 * Disallow specific values in form
 */
class ValueBlacklistStrategy extends AbstractStrategy {

	private $blacklist = [];

	private $fieldNames;

	/**
	 * e.g.:
	 * $blacklist => [
	 * 		'name' => ['firstname' => 'John', 'lastname' => 'Doe'],
	 * 		'firstname' => 'John'
	 * ]
	 *
	 * @param array $blacklist
	 * @param array $fieldNames
	 */
	public function __construct(array $blacklist, array $fieldNames = []) {
		$this->blacklist = $blacklist;
		$this->fieldNames = $fieldNames;
	}

	public function detect(Form $form, Request $request): bool {

		$values = $form->getFieldValues($this->fieldNames);

		$checkValue = function ($configField, $forbiddenValue) use ($values) {
			foreach ($values as $field => $value) {
				if (
					is_string($value) &&
					strpos($field, $configField) !== false &&
					$value == $forbiddenValue
				) {
					return true;
				}
			}

			return false;
		};

		$matches = 0;
		foreach($this->blacklist as $configField => $forbiddenValue) {

			$checked = [];
			if (is_array($forbiddenValue)) {
				foreach ($forbiddenValue as $configInnerField => $innerForbiddenValue) {
					$checked[] = $checkValue($configInnerField, $innerForbiddenValue);
				}
			} else {
				$checked[] = $checkValue($configField, $forbiddenValue);
			}

			if (!in_array(false, $checked)) {
				$matches++;
			}
		}

		if($matches > 0) {
			$this->info(['matches' => $matches]);
			return true;
		}

		return false;
	}

}
