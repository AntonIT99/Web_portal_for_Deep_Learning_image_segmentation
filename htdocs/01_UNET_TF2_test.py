import glob
import os
import sys
from datetime import datetime

from tqdm import tqdm 

import numpy as np

import matplotlib.pyplot as plt
#%matplotlib inline
plt.rcParams["figure.figsize"] = (15,15)

import cv2
import tensorflow as tf

# add keras-unet python sources to the path
if '../' in sys.path: 
    print(sys.path)
else: 
    sys.path.append('./')
    print(sys.path)

if 'model' in locals(): 
    print("deleting model")
    del model    
    
# select the device (CPU or GPU) to run on
num_CPU = 1
num_cores = 4

# KERNEL msut be restarted if you change GPU 0 -> 1 or 1 -> 0 (cannot change runtime after initialization)
GPU = 0  # GPU = 0 : CPU Only ; GPU = 1 : use GPU

physical_gpu_devices = tf.config.list_physical_devices('GPU')
physical_cpu_devices = tf.config.list_physical_devices('CPU')
print(physical_gpu_devices)
print(physical_cpu_devices)

if GPU:
#    try:  # !! commented to see error
        #tf.config.experimental.set_memory_growth(physical_gpu_devices[0], True)ass
    tf.config.set_visible_devices(physical_gpu_devices[0], 'GPU')
#        tf.config.LogicalDeviceConfiguration(memory_limit=7500, experimental_priority=None)
    visible_devices = tf.config.get_visible_devices()
    print(visible_devices)  
    for device in visible_devices:
        print(device)
#    except:
      # Invalid device or cannot modify virtual devices once initialized.
#      pass
    tf.config.threading.set_intra_op_parallelism_threads(num_CPU)
    tf.config.threading.set_inter_op_parallelism_threads(num_cores) 
else:
    try:
      # Disable all GPUS
      tf.config.set_visible_devices([], 'GPU')
      visible_devices = tf.config.get_visible_devices()
      for device in visible_devices:
        print(device)
        assert device.device_type != 'GPU'
    except:
      # Invalid device or cannot modify virtual devices once initialized.
      pass
    tf.config.threading.set_intra_op_parallelism_threads(num_CPU)
    tf.config.threading.set_inter_op_parallelism_threads(num_cores)



### Pre processing images
    
# test
#image_path="./dlss21_ho4_data/"
test_masks_files     = glob.glob("./segmentation/labels/*.png")
test_images_e8_files = glob.glob("./segmentation/images/*_e8.png")
test_images_e5_files = glob.glob("./segmentation/images/*_e5.png")
test_images_e2_files = glob.glob("./segmentation/images/*_e2.png")

filenames_e2 = [os.path.basename(x) for x in test_images_e2_files]
filenames_e5 = [os.path.basename(x) for x in test_images_e5_files]
filenames_e8 = [os.path.basename(x) for x in test_images_e8_files]


# Data related values
IMG_SIZE = 96

JUPYTER_DISPLAY_ON = True

model_path = './'
model_name = 'Unet_f32_b16_l5_do0.1_Std_BN_input96.h5'
model_filename = model_path + model_name 



### Load data
from keras_unet.utils import ReadImages, ReadMasks

test_images_e2_files.sort()
test_images_e5_files.sort()
test_images_e8_files.sort()
test_masks_files.sort()

print( " testing   :  ", len(test_images_e2_files), len(test_images_e5_files), len(test_images_e8_files), len(test_masks_files) )

permutation_test = np.random.permutation( len(test_images_e8_files))
test_images_e2_files_rnd=[test_images_e2_files[i] for i in permutation_test]
test_images_e5_files_rnd=[test_images_e5_files[i] for i in permutation_test]
test_images_e8_files_rnd=[test_images_e8_files[i] for i in permutation_test]
test_masks_files_rnd=[test_masks_files[i] for i in permutation_test]

# reading files
X_test_e8 = ReadImages(test_images_e8_files_rnd, size=(IMG_SIZE, IMG_SIZE), crop=(30,30,330,330))
X_test_e5 = ReadImages(test_images_e5_files_rnd, size=(IMG_SIZE, IMG_SIZE), crop=(30,30,330,330))
X_test_e2 = ReadImages(test_images_e2_files_rnd, size=(IMG_SIZE, IMG_SIZE), crop=(30,30,330,330))
y_test = ReadMasks(test_masks_files_rnd, size=(IMG_SIZE, IMG_SIZE), crop=(30,30,330,330))

print(" Shape test : ", X_test_e2.shape, X_test_e5.shape, X_test_e8.shape, y_test.shape)
print(" Type test : ", X_test_e2.dtype, X_test_e5.dtype, X_test_e8.dtype, y_test.dtype)

#print(test_images_e8_files_rnd[3])
#print(test_images_e8_files_rnd[6])


### Plot images + masks + overlay (mask over original)
from keras_unet.visualization import plot_overlay_segmentation, plot_compare_segmentation, save_segmentation, save_overlay_segmentation

#plot_overlay_segmentation(X_test_e8[3:15], y_test[3:15])


### Load the network and its weights
from keras_unet.metrics import iou, iou_thresholded
from keras_unet.losses import dice_loss, dice_coef, adaptive_loss
from tensorflow.keras import models

tf.keras.backend.clear_session()

# load the network with its custom functions
loaded_model = models.load_model(model_filename, custom_objects={'dice_coef': dice_coef, 'adaptive_loss': adaptive_loss, 'dice_loss': dice_loss})

# display the network
loaded_model.summary()


### Predict segmentations on the whole test set using the network
y_pred_e8 = loaded_model.predict(X_test_e8, batch_size=1, verbose=1) # GPU Size
y_pred_e5 = loaded_model.predict(X_test_e5, batch_size=1, verbose=1)
y_pred_e2 = loaded_model.predict(X_test_e2, batch_size=1, verbose=1)

loss, dice_coef = loaded_model.evaluate(x=X_test_e8, y=y_test, batch_size=1, verbose=1) # 
print(f"loss : {loss}   dice_coeff : {dice_coef}")


### Plot images : MRI with overlay of ground truth + MRI with prediction overlay
#N_b = 0
#N_e = 10
#plot_compare_segmentation(X_test_e8[N_b:N_e], y_test[N_b:N_e], y_pred[N_b:N_e], " ", spacing=(1,1), step=1)

for i in range(len(test_images_e8_files)):
  save_segmentation(X_test_e8[i], y_pred_e8[i], "./segmentation/images/results/{}".format(filenames_e8[i]))
  print("Saved file {}".format(filenames_e8[i]))
  
for i in range(len(test_images_e5_files)):
  save_segmentation(X_test_e5[i], y_pred_e5[i], "./segmentation/images/results/{}".format(filenames_e5[i]))
  print("Saved file {}".format(filenames_e5[i]))
  
for i in range(len(test_images_e2_files)):
  save_segmentation(X_test_e2[i], y_pred_e2[i], "./segmentation/images/results/{}".format(filenames_e2[i]))
  print("Saved file {}".format(filenames_e2[i]))

print("Done")


### Evalaution with DICE/Hausdorff distance and Average symmetric surface distance
#from keras_unet.evaluation import  evaluate_segmentation, evaluate_set

#from keras_unet import evaluation
#dice, hausdorff, assds = evaluate_segmentation(y_test[1], y_pred[1], voxel_spacing = [1, 1])
#print("Dice:", dice)
#print("hausdorff:", hausdorff)
#print("assds",assds)

#dice_all, hausdorff_all, assd_all, valid_all = evaluate_set(y_test, y_pred)

#print("dice_all", dice_all)
#print("hausdorff", hausdorff_all)
#print("assd", assd_all)

#import pandas as pd
#from IPython.display import display, HTML 

#overall_results = np.column_stack((dice_all, hausdorff_all, assd_all))
#print(overall_results)

# Graft our results matrix into pandas data frames 
#overall_results_df = pd.DataFrame(data=overall_results, index = ["All", "1", "2", "3", "4", "5"], 
                                  #columns=["Dice", "Hausdorff", "ASSD"]) 

# Display the data as HTML tables and graphs
#display(HTML(overall_results_df.to_html(float_format=lambda x: '%.3f' % x)))
#overall_results_df.plot(kind='bar', figsize=(10,6)).legend() #bbox_to_anchor=(1.6,0.9))

#print(overall_results_df.to_latex(float_format=lambda x: '%.3f' % x)) # column_format='cccc'
