This repository contains all the source files, executables, and data bases of the project.
It only requires the "Unet_f32_b16_l5_do0.1_Std_BN_input96.h5" file for the python deep learning neural network to function.
This file could not be included because of its size (475 MB).
You can download it here: https://www.creatis.insa-lyon.fr/~grenier/wp-content/uploads/teaching/DeepLearning/Unet_f32_b16_l5_do0.1_Std_BN_input96.h5
It must be placed in the "htdocs" directory.

The PHP/HTML code of the website as well as the python scripts can be found here: ./htdocs/
The database of the user accounts can be found in the following directory: ./var/mysql/phplogin/

How to install this project:

Under CentOS 7:

For setup with XAMPP
place ./htdocs/ in /opt/lampp/
place ./var/ in /opt/lampp/

For setup with apache (httpd) server and MariaDB/MySQL + phpMyAdmin
./htdocs/ matches the following directory /var/www/html
./var/mysql/ matches the following directory /var/lib/mysql/
