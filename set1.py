import sys, os, json, csv
import pandas as pd

import cv2
import numpy as np
from skimage.filters import threshold_otsu
from PIL import Image
import matplotlib
matplotlib.use('Agg')  # prevent GUI windows
import matplotlib.pyplot as plt
import warnings
warnings.filterwarnings("ignore")

# --- Read config ---
config_file = sys.argv[1]
with open(config_file, 'r') as f:
    config = json.load(f)

# --- Folder paths ---
upload_dir    = os.path.abspath("high_uploads")
excel_dir     = os.path.join(upload_dir, "excel_files")
jpg_dir       = os.path.join(upload_dir, "jpg_files")
param_dir     = os.path.join(upload_dir, "parameters_files")

# Create folders if missing
for d in [excel_dir, jpg_dir, param_dir]:
    os.makedirs(d, exist_ok=True)

all_results = []

# --- Helper: Spatially Adaptive Canny ---
def spatially_adaptive_canny(img, grid_size=(4,4), blur_kernel=(5,5),
                              sigma=1.5, threshold_ratio=0.4, bottom_multiplier=3.0):
    h, w = img.shape
    h_step = h // grid_size[0]
    w_step = w // grid_size[1]
    edge_map = np.zeros_like(img)
    for i in range(grid_size[0]):
        for j in range(grid_size[1]):
            y0, y1 = i*h_step, min((i+1)*h_step, h)
            x0, x1 = j*w_step, min((j+1)*w_step, w)
            patch = img[y0:y1, x0:x1]
            if patch.size == 0: continue
            if y0 > h//2:
                high = 50 * bottom_multiplier
                low = high * threshold_ratio
            else:
                th = threshold_otsu(patch)
                high = th
                low = high * threshold_ratio
            blurred_patch = cv2.GaussianBlur(patch, blur_kernel, sigma)
            edges = cv2.Canny(blurred_patch, int(low), int(high))
            edge_map[y0:y1, x0:x1] = edges
    return edge_map

# --- Process each file ---
for file_name in config['files']:
    try:
        full_path = os.path.join(upload_dir, file_name)
        base_name = os.path.splitext(file_name)[0].strip()

        # Step 1: Grayscale, resize, denoise
        image = Image.open(full_path).convert("L")
        gray_array = np.array(image)
        scale_percent = 20
        width = int(gray_array.shape[1]*scale_percent/100)
        height = int(gray_array.shape[0]*scale_percent/100)
        resized_img = cv2.resize(gray_array, (width, height))
        denoised_img = cv2.medianBlur(resized_img, 3)

        # Step 2: Edges
        edges = spatially_adaptive_canny(denoised_img)

        # Step 3: Circle detection
        blurred = cv2.GaussianBlur(denoised_img, (5,5), 1.3)
        edges_circle = cv2.Canny(blurred, 30, 100)
        circles = cv2.HoughCircles(edges_circle, cv2.HOUGH_GRADIENT, dp=1.75, minDist=35,
                                   param1=100, param2=35, minRadius=5, maxRadius=40)

        output_img = cv2.cvtColor(denoised_img, cv2.COLOR_GRAY2BGR)
        circle_data, circle_radii = [], []
        nm_per_pixel = 2

        if circles is not None:
            circles = np.uint16(np.around(circles))
            for idx, (x, y, r) in enumerate(circles[0,:], start=1):
                cv2.circle(output_img, (x, y), r, (0,255,0), 2)
                cv2.circle(output_img, (x, y), 2, (0,0,255), 3)
                cv2.putText(output_img, str(idx), (x-10, y-10),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255,0,0), 2)
                perimeter_nm = 2*np.pi*r*nm_per_pixel
                area_nm2 = np.pi*(r**2)*(nm_per_pixel**2)
                circle_data.append((idx, int(r), perimeter_nm, area_nm2))
                circle_radii.append(int(r))

        detected_count = len(circle_radii)

        # Step 4: Histogram
        histogram_path = os.path.join(jpg_dir, f"{base_name}_histogram.png")
        if circle_radii:
            plt.figure(figsize=(6,4))
            bins = np.arange(min(circle_radii), max(circle_radii)+2)-0.5
            plt.hist(circle_radii, bins=bins, edgecolor='black', rwidth=0.8)
            plt.xlabel("Radius (px)")
            plt.ylabel("Count")
            plt.title("Histogram of Detected Circle Radii")
            plt.grid(True, linestyle='--', alpha=0.6)
            plt.savefig(histogram_path, bbox_inches='tight')
            plt.close()
        else:
            plt.imsave(histogram_path, np.zeros((200,300)), cmap='gray')

        # Step 5: Contours
        _, binary_thresh = cv2.threshold(denoised_img,0,255,cv2.THRESH_BINARY+cv2.THRESH_OTSU)
        binary_thresh = cv2.bitwise_not(binary_thresh)
        contours, _ = cv2.findContours(binary_thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        perimeter_img = cv2.cvtColor(denoised_img, cv2.COLOR_GRAY2BGR)
        cv2.drawContours(perimeter_img, contours, -1, (255,0,0), 2)
        total_perimeter_pixels = sum(cv2.arcLength(cnt,True) for cnt in contours) if contours else 0.0
        area_pixels = int(np.sum(binary_thresh==0))
        perimeter_nm_total = total_perimeter_pixels * nm_per_pixel
        area_nm2_total = area_pixels * (nm_per_pixel**2)

        # Step 6: Save images (all in jpg_dir)
        edges_path = os.path.join(jpg_dir, f"{base_name}_edges.png")
        circles_path_img = os.path.join(jpg_dir, f"{base_name}_circles.png")
        contours_path = os.path.join(jpg_dir, f"{base_name}_contours.png")
        threshold_path = os.path.join(jpg_dir, f"{base_name}_threshold.png")
        plt.imsave(edges_path, edges, cmap='gray')
        plt.imsave(circles_path_img, cv2.cvtColor(output_img, cv2.COLOR_BGR2RGB))
        plt.imsave(contours_path, cv2.cvtColor(perimeter_img, cv2.COLOR_BGR2RGB))
        plt.imsave(threshold_path, binary_thresh, cmap='gray')

        # Step 7: CSV
        csv_file = os.path.join(excel_dir, f"{base_name}.csv")
        with open(csv_file,'w',newline='') as f:
            writer = csv.writer(f)
            writer.writerow(["Circle No","Radius(px)","Perimeter(nm)","Area(nm²)"])
            if circle_data:
                for row in circle_data: writer.writerow(row)
            else:
                writer.writerow(["No circles detected"])
            writer.writerow([])
            writer.writerow(["Summary","Value"])
            writer.writerow(["Total Perimeter (px)", total_perimeter_pixels])
            writer.writerow(["Total Perimeter (nm)", perimeter_nm_total])
            writer.writerow(["Total Area (px²)", area_pixels])
            writer.writerow(["Total Area (nm²)", area_nm2_total])

        # Step 8: Parameters JSON
# Step 8: Save summary as Excel only
        summary = {
               "File": file_name,
               "Detected Circles": detected_count,
               "Total Perimeter (px)": total_perimeter_pixels,
               "Total Perimeter (nm)": perimeter_nm_total,
               "Total Area (px²)": area_pixels,
               "Total Area (nm²)": area_nm2_total
               }

# Save Excel summary (no JSON)
        summary_excel_path = os.path.join(param_dir, f"{base_name}.xlsx")
        df_summary = pd.DataFrame(list(summary.items()), columns=["Parameter", "Value"])
        df_summary.to_excel(summary_excel_path, index=False)

# --- Store results for frontend ---
        all_results.append({
            "file": file_name,
            "circle_data":[{"index":i,"radius":r,"perimeter":p,"area":a} 
                   for i,r,p,a in circle_data],
            "summary_excel": summary_excel_path,   # <- only Excel path now
            "images":[edges_path, circles_path_img, contours_path, threshold_path, histogram_path],
            "csv": csv_file
            })

    except Exception as e:
        all_results.append({"file": file_name, "error": str(e)})

# --- Output final JSON ---
print(json.dumps(all_results, indent=2))
