EasyE
============

**Note this library is in development and isn't fully working yet**

The EasyE library is a collection of simple, easy to use PHP image processing classes using the GD extension.

There are currently two main components to the library:

1.  Image Cropping
2.  Image Resizing

Example of Cropping
-------------------------
<pre><code>// Load the Cropper Class.
require 'EasyE/Cropper.php';

// Create a new Cropper object.
$cropper = new Cropper;

// Make a square crop of image.png and save it as image-square.png
$res = $cropper->setSourceFileLocation('/path/to/image.png')
                ->setDestinationFileLocation('/path/to/image-square.png')
                ->cropToSquare();  
                
// View the result data:
print_r($res);</code></pre>

Example of Resizing
-------------------------
<pre><code>// Load the Resizer Class.
require 'EasyE/Resizer.php';

// Create a new Resizer object.
$resizer = new Resizer;

// Resize image-square.png to have a miximum height and width of 75 pixels. Save it as image-thumb.png
$res = $resizer->setSourceFileLocation('/path/to/image-square.png')
                ->setDestinationFileLocation('/path/to/image-thumb.png')
                ->resize(75, 75);  
                
// View the result data:
print_r($res);</code></pre>

More Details:
-------------------------
This library is still in development and not yet production ready.
