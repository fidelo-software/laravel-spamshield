<?php

namespace FideloSoftware\Spam\Strategies;

use FideloSoftware\Spam\Contracts\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Honeypot
 *
 * Include number of hidden input fields that will be filled by bots but not by users.
 */
class HoneypotStrategy extends AbstractStrategy {

	private $numberOfFields;

	private $attrPool = [
		'contact_me_by_fax', 'additional_information_about_me', 'my_phone_number', 'address_zip_field',
		'my_allergies', 'address_city_field', 'my_mobile_phone_number', 'my_private_email',
		// to be continued
	];

	public function __construct(int $numberOfFields = 3) {
		$this->numberOfFields = $numberOfFields;
	}

	public function html(Form $form): string {

		$fieldNames = $this->generateRandomAttrArray($this->numberOfFields);

		$html = '';
		// Include defined number of fields as input fields.
		foreach($fieldNames as $nameAttr) {
			$html .= sprintf('<div class=""><label for="%s"></label><input type="text" name="%s" id="%s" value="" tabindex="-1" autocomplete="off" placeholder="Please enter" /></div>', $nameAttr, $nameAttr, $nameAttr);
		}

		return $html;
	}

	public function detect(Form $form, Request $request): bool {

		// Check if one of the hidden input fields is filled with value so that we can assume it was filled by bot.
		$found = (new Collection($this->attrPool))
			->first(function($nameAttr) use ($request) {
				return ($request->exists($nameAttr) && !empty($request->input($nameAttr)));
			});

		if($found !== null) {
			$this->info(['attr' => $found]);
			return true;
		}

		return false;
	}

	/**
	 * Generates a random array with unique attributes from attributes pool.
	 *
	 * @param int $count
	 * @return array
	 * @throws \Exception
	 */
	private function generateRandomAttrArray(int $count): array {

		$fieldNames = [];

		for($i = 1; $i <= $count; ++$i) {
			do {
				$index = random_int(0, (count($this->attrPool) - 1));
			} while (in_array($this->attrPool[$index], $fieldNames));

			$fieldNames[] = $this->attrPool[$index];
		}

		return $fieldNames;
	}

}
