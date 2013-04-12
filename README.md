EasyE
============

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

// Make a square crop of image.png and save it as image-cropped.png
$res = $cropper->setSourceFileLocation('/path/to/image.png')
                ->setNewFileLocation('/path/to/image-cropped.png')
                ->cropToSquare();  
                
// View the result data:
echo '\<pre\>';
print_r($res);
echo '\</pre\>';</code></pre>
