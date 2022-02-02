#!/bin/bash
#PBS -N segmentation
#PBS -q batch
#PBS -S /bin/bash
python3 01_UNET_TF2_test.py 
