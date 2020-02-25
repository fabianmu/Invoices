<?php

//This file is part of eavio/invoices.

namespace eavio\invoices\Classes;

use Carbon\Carbon;
use eavio\invoices\Traits\Setters;
use Illuminate\Support\Collection;
use Storage;


//This is the Invoice class.
class Invoice
{
    use Setters;

    /**
     * Invoice discount.
     *
     * @var int
     */
	public $discount = null;
    /**
     * Invoice name.
     *
     * @var string
     */
    public $name;

    /**
     * Invoice template.
     *
     * @var string
     */
    public $template;

    /**
     * Invoice item collection.
     *
     * @var Illuminate\Support\Collection
     */
    public $items;

    /**
     * Invoice currency.
     *
     * @var string
     */
    public $currency;

    /**
     * Invoice number.
     *
     * @var int
     */
    public $number = null;

    /**
     * Invoice decimal precision.
     *
     * @var int
     */
    public $decimals;

    /**
     * Invoice logo.
     *
     * @var string
     */
    public $logo;

    /**
     * Invoice Logo Height.
     *
     * @var int
     */
    public $logo_height;

    /**
     * Invoice Date.
     *
     * @var Carbon\Carbon
     */
    public $date;

    /**
     * Invoice Notes.
     *
     * @var string
     */
    public $notes;

    /**
     * Invoice Business Details.
     *
     * @var array
     */
    public $business_details;

    /**
     * Invoice Customer Details.
     *
     * @var array
     */
    public $customer_details;

    /**
     * Invoice Footnote.
     *
     * @var array
     */
    public $footnote;

    /**
     * Invoice Tax Rates Default.
     *
     * @var array
     */
    public $tax_rates;

    /**
     * Invoice Due Date.
     *
     * @var Carbon\Carbon
     */
    public $due_date = null;

    /**
     * Invoice pagination.
     *
     * @var boolean
     */
    public $with_pagination;

    /**
     * Invoice header duplication.
     *
     * @var boolean
     */
    public $duplicate_header;

    /**
     * Stores the PDF object.
     *
     * @var Dompdf\Dompdf
     */
    private $pdf;

    /**
     * Create a new invoice instance.
     *
     * @method __construct
     *
     * @param string $name
     */
    public function __construct($name = 'Invoice')
    {
        $this->name = $name;
        $this->template = 'default';
        $this->items = Collection::make([]);
        $this->currency = config('invoices.currency');
        $this->decimals = config('invoices.decimals');
        $this->logo = config('invoices.logo');
        $this->logo_height = config('invoices.logo_height');
        $this->date = Carbon::now();
        $this->business_details = Collection::make(config('invoices.business_details'));
        $this->customer_details = Collection::make([]);
        $this->footnote = config('invoices.footnote');
        $this->tax_rates = config('invoices.tax_rates');
        $this->due_date = config('invoices.due_date') != null ? Carbon::parse(config('invoices.due_date')) : null;
        $this->with_pagination = config('invoices.with_pagination');
        $this->duplicate_header = config('invoices.duplicate_header');
		$this->discount = 0;
		$this->date_of_service = config('invoices.date_of_service');
		$this->footer_logo = config('invoices.footer_logo');
		$this->tax_number = config('invoices.tax_number');
		$this->vats = Array(Array(),Array());
    }

    /**
     * Return a new instance of Invoice.
     *
     * @method make
     *
     * @param string $name
     *
     * @return eAvio\Invoices\Classes\Invoice
     */
    public static function make($name = 'Invoice')
    {
        return new self($name);
    }

    /**
     * Select template for invoice.
     *
     * @method template
     *
     * @param string $template
     *
     * @return self
     */
    public function template($template = 'default')
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Adds an item to the invoice.
     *
     * @method addItem
     *
     * @param string $name
     * @param int    $price
     * @param string $unit
     * @param int    $ammount
     * @param int    $vat
     * @param float  $totalPrice
     * @param string $id
     * @param string $imageUrl
     *
     * @return self
     */

    public function addItem($name, $price, $unit, $ammount = 1, $vat, $id = '-', $imageUrl = null)
    {
        $this->items->push(Collection::make([
            'name'       => $name,
            'price'      => $price,
			'unit'       => $unit,
            'ammount'    => $ammount,
			'vat'		 => $vat,
            'totalPrice' => number_format(($price * $ammount) + $this->vatPrice(bcmul($price, $ammount, $this->decimals), $vat), $this->decimals),
            'id'         => $id,
            'imageUrl'   => $imageUrl,
        ]));

         // Saves the vats (without duplicates) into an array with the vat percentage if the vat percentage is not a duplicate
		if(!in_array($vat, $this->vats[0]) and $vat != 0){
			array_push($this->vats[0], $vat);
			array_push($this->vats[1], $this->vatPrice($price * $ammount, $vat));
		}

         // Else if the vat isn't 0, finds the index of the vat percentage and adds the value of the current vat into the found index field
		else if($vat != 0){
			$key = array_search($vat, $this->vats[0]);
			$value = $this->vats[1][$key];
			$value += $this->vatPrice($price * $ammount, $vat);
			$this->vats[1][$key] = $value;
		}
        return $this;
    }

    /**
     * Returns the price of the discount based on the total price
     *
     * @method discountPrice
     *
     * @return float
     */
    public function discountPrice(){
        return $this->subTotalPrice() * (100-$this->discount) / 100;
    }

    /**
     * Returns the formatted discount price
     *
     * @method discountPriceFormatted
     *
     * @return int
     */
    public function discountPriceFormatted(){
        return number_format($this->subTotalPrice() - $this->discountPrice(), $this->decimals);
    }

    /**
     * Returns the VAT price
     *
     * @method vatPrice
     *
     * @return float
     */
    public function vatPrice($price, $percentage){
        return ($percentage/100) * $price;
    }

    /**
     * Returns the VAT percentage for the specified index
     *
     * @method getVat
     *
     * @param int $id
     *
     * @return int
     */
    public function getVat($id){
        return $this->vats[0][$id];
    }

    /**
     * Returns the VAT value for the specified index
     *
     * @method getVatValue
     *
     * @param int $id
     *
     * @return int
     */
    public function getVatValue($id){
        return number_format($this->vats[1][$id], $this->decimals);
    }

    /**
     * Returns the price without VAT and without discount
     *
     * @method noVatPrice
     *
     * @return float
     */
    public function noVatPrice()
    {
        return $this->items->sum(function ($item) {
            return ($item['price'] * $item['ammount']);
        });
    }

    /**
     * Pop the last invoice item.
     *
     * @method popItem
     *
     * @return self
     */
    public function popItem()
    {
        $this->items->pop();

        return $this;
    }

    /**
     * Return the currency object.
     *
     * @method formatCurrency
     *
     * @return stdClass
     */
    public function formatCurrency()
    {
        $currencies = json_decode(file_get_contents(__DIR__.'/../Currencies.json'));
        $currency = $this->currency;

        return $currencies->$currency;
    }

    /**
     * Return the subtotal invoice price.
     *
     * @method subTotalPrice
     *
     * @return int
     */
    private function subTotalPrice()
    {
        return $this->items->sum(function ($item) {
            return ($item['price'] * $item['ammount']) + $this->vatPrice(bcmul($item['price'], $item['ammount'], $this->decimals), $item['vat']);
        });
    }



    /**
     * Return formatted sub total price.
     *
     * @method subTotalPriceFormatted
     *
     * @return int
     */
    public function subTotalPriceFormatted()
    {
        return number_format($this->subTotalPrice(), $this->decimals);
    }

    /**
     * Return the total invoce price after aplying the tax.
     *
     * @method totalPrice
     *
     * @return int
     */
    private function totalPrice()
    {
        return bcadd($this->discountPrice(), $this->taxPrice(), $this->decimals);
    }

    /**
     * Return formatted total price.
     *
     * @method totalPriceFormatted
     *
     * @return int
     */
    public function totalPriceFormatted()
    {
        return number_format($this->totalPrice(), $this->decimals);
    }

    /**
     * taxPrice.
     *
     * @method taxPrice
     *
     * @return float
     */
    private function taxPrice(Object $tax_rate = null)
    {
        if (is_null($tax_rate)) {
            $tax_total = 0;
            foreach($this->tax_rates as $taxe){
                if ($taxe['tax_type'] == 'percentage') {
                    $tax_total += bcdiv(bcmul($taxe['tax'], $this->subTotalPrice(), $this->decimals), 100, $this->decimals);
                    continue;
                }
                $tax_total += $taxe['tax'];
            }
            return $tax_total;
        }

        return bcdiv(bcmul($tax_rate->tax, $this->subTotalPrice(), $this->decimals), 100, $this->decimals);
    }

    /**
     * Return formatted tax.
     *
     * @method taxPriceFormatted
     *
     * @return int
     */
    public function taxPriceFormatted($tax_rate)
    {
        return number_format($this->taxPrice($tax_rate), $this->decimals);
    }

    /**
     * Generate the PDF.
     *
     * @method generate
     *
     * @return self
     */
    private function generate()
    {
        $this->pdf = PDF::generate($this, $this->template);

        return $this;
    }

    /**
     * Downloads the generated PDF.
     *
     * @method download
     *
     * @param string $name
     *
     * @return response
     */
    public function download($name = 'invoice')
    {
        $this->generate();

        return $this->pdf->stream($name);
    }

    /**
     * Save the generated PDF.
     *
     * @method save
     *
     * @param string $name
     *
     */
    public function save($name = 'invoice.pdf')
    {
        $invoice = $this->generate();

        Storage::put($name, $invoice->pdf->output());
    }

    /**
     * Show the PDF in the browser.
     *
     * @method show
     *
     * @param string $name
     *
     * @return response
     */
    public function show($name = 'invoice')
    {
        $this->generate();

        return $this->pdf->stream($name, ['Attachment' => false]);
    }

    /**
     * Return true/false if one item contains image.
     * Determine if we should or shouldn't display the image column on the invoice.
     *
     * @method shouldDisplayImageColumn
     *
     * @return boolean
     */
    public function shouldDisplayImageColumn()
    {
        foreach($this->items as $item){
            if($item['imageUrl'] != null){
                return true;
            }
        }
    }
}
