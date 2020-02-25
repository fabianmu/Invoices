## What is Invoices?

Invoices is a Laravel library that generates a PDF invoice for your customers. The PDF can be either downloaded or
streamed in the browser. It's highly customizable and you can modify the whole output view as well.

## Sample Invoice

This is a sample invoice generated using this library:

![Sample Invoice](https://i.ibb.co/BVkbsT0/Invoice-Demo.png)

```php
$invoice = Invoice::make()
    ->name('Invoice')
    ->logo('https://process.arts.ac.uk/sites/default/files/online_-id_ban_large-820_x_150.jpg')
    ->footer_logo('https://www.poutskincare.co.za/wp-content/uploads/2016/07/POUT_Background-900x100.png')
    ->addItem('Item 1', 10, 'min', 13, 12, 1337)
    ->addItem('Item 2', 5, 'hr', 6, 4, 5232)
    ->addItem('Item 3', 3, 'min', 8, 16, 6135)
    ->addItem('Item 4', 16, 'min', 6, 4, 5313)
    ->duplicate_header(true)
    ->number(1234567890)
    ->due_date(Carbon::now()->addMonths(1))
    ->date(Carbon::now())
    ->notes('Tese are the notes')
    ->discount(15)
    ->date_of_service(Carbon::now())
    ->customer([
        'name' => 'John Doe',
        'address' => 'John Doe
            Love alley 3000
            1234 America',
        'id' => 241256,
        'vat_payer' => 0
    ])
    ->download('demo');
```

## License

```
MIT License

Copyright (c) 2020 Jernej Žuraj

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

```
