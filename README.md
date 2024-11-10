# Image Color Processor

The following allowes you to upload an image and get the 5 most dominant colors in it, using percentage values and RGB colors.

## Technical explanation:
1. For the conversion of the image into a bitmap I chose using the built-in image module in php-*the GD extension*.
The reason behined it is that the common image formats (png, jpeg) are actually compressed-meaning that in order to get an actual byte representation of the RGB values I will need to decompress the files accordingly, which will require implementing my own decoder of the files-complex, time consuming and will 99% chance that it will be way more inefficient than what already exists.

2. For the checksum I had a bit of struggle regarding the different encodings between the JS client and PHP server, I resorted using chatGPT which suggested converting the file into hex which resolved the issue of the binary strings having various problematic characters that resulted in the checksum differences.


### References used:
- How to split the file upload into chunks-https://stackoverflow.com/questions/50121917/split-an-uploaded-file-into-multiple-chunks-using-javascript
- The php manual-https://www.php.net/docs.php
- The GD function for image creation-https://www.php.net/manual/en/function.imagecreatefromjpeg.php
- Merging the file back on the server-https://stackoverflow.com/questions/36045690/merging-file-chunks-in-php

``` Don't forget to enable the GD module in order to use the server's function to convert the image into an RGB array```