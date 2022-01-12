<?php

namespace FideloSoftware\Spam\Strategies;

use FideloSoftware\Spam\Contracts\Form;
use Illuminate\Http\Request;

/**
 * Links
 *
 * Allow only a specific number of links in input values.
 */
class LinkStrategy extends AbstractStrategy {

	private $numberOfLinksAllowed;

	private $fieldNames;

	public function __construct(int $numberOfLinksAllowed = 0, array $fieldNames = []) {
		$this->numberOfLinksAllowed = $numberOfLinksAllowed;
		$this->fieldNames = $fieldNames;
	}

	public function detect(Form $form, Request $request): bool {

		$values = $form->getFieldValues($this->fieldNames);

		$linkCount = 0;
		foreach ($values as $field => $value) {
			// Allow links in URL name attributes
			if (!is_string($value) || strpos($field, 'url') !== false) {
				continue;
			}

			preg_match_all('@http://|https://|ftp://@', $value, $result);
			if (isset($result[0])) {
				$linkCount += count($result[0]);
			}
		}

		if($linkCount > $this->numberOfLinksAllowed) {
			// Maximum number of allowed link exceeded
			$this->info(['found' => $linkCount]);
			return true;
		}

		return false;
	}

}
