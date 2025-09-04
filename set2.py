import sys, os, json, cv2, numpy as np, pandas as pd
from matplotlib.backends.backend_pdf import PdfPages
import matplotlib.pyplot as plt

# --- Arguments from PHP ---
config_file = sys.argv[1]  # uploads/config.json
uploads_folder = sys.argv[2]  # uploads/

# --- Load configuration ---
with open(config_file,'r') as f:
    config = json.load(f)

scale_nm_per_pixel = float(config.get('scale', 1.0))
threshold_values = config.get('thresholds', [])
uploaded_files = config.get('files', [])

if not uploaded_files:
    print("WARNING : No uploaded files found in config")
    sys.exit(1)

# --- Create folders for outputs ---
binary_dir = os.path.join(uploads_folder,"binary_aggregates")
excel_dir = os.path.join(uploads_folder,"excel_files")
pdf_dir = os.path.join(uploads_folder,"pdf_files")
os.makedirs(binary_dir, exist_ok=True)
os.makedirs(excel_dir, exist_ok=True)
os.makedirs(pdf_dir, exist_ok=True)

combined_excel_path = os.path.join(excel_dir,"combined.xlsx")
combined_pdf_path = os.path.join(pdf_dir,"combined.pdf")

min_area_thresh = 5

# --- Process only uploaded images ---
with pd.ExcelWriter(combined_excel_path, engine='openpyxl') as combined_writer, PdfPages(combined_pdf_path) as combined_pdf:

    for idx, image_name in enumerate(uploaded_files):
        manual_thresh_value = threshold_values[idx] if idx < len(threshold_values) else 128
        image_path = os.path.join(uploads_folder, image_name)
        print(f"\nProcessing: {image_name}  threshold={manual_thresh_value}")

        original = cv2.imread(image_path)
        if original is None:
            print(f"WARNING : Skipping {image_name}, could not load.")
            continue

        height_crop = int(original.shape[0]*0.93)
        cropped = original[:height_crop,:]

        gray = cv2.cvtColor(cropped, cv2.COLOR_BGR2GRAY)
        output_image = cv2.cvtColor(gray.copy(), cv2.COLOR_GRAY2BGR)
        blurred = cv2.bilateralFilter(gray, d=9, sigmaColor=75, sigmaSpace=75)

        _, thresh = cv2.threshold(blurred, manual_thresh_value, 255, cv2.THRESH_BINARY_INV)

        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        contours = sorted(contours, key=lambda c: cv2.boundingRect(c)[1]*10000 + cv2.boundingRect(c)[0])

        aggregate_count = 0
        centroid_data = []

        for cnt in contours:
            if cv2.contourArea(cnt) < min_area_thresh:
                continue

            x, y, w, h = cv2.boundingRect(cnt)
            padding = max(10, int(0.1*max(w,h)))
            x1, y1 = max(x-padding,0), max(y-padding,0)
            x2, y2 = min(x+w+padding, gray.shape[1]), min(y+h+padding, gray.shape[0])
            aggregate_crop = gray[y1:y2, x1:x2]

            _, binary_crop = cv2.threshold(aggregate_crop, manual_thresh_value, 255, cv2.THRESH_BINARY_INV)

            aggregate_filename = f"{os.path.splitext(image_name)[0]}_aggregate_{aggregate_count+1}.png"
            cv2.imwrite(os.path.join(binary_dir, aggregate_filename), binary_crop)

            contours_crop, _ = cv2.findContours(binary_crop, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            if not contours_crop:
                continue

            combined_mask = np.zeros_like(binary_crop)
            cv2.drawContours(combined_mask, contours_crop, -1, 255, thickness=cv2.FILLED)
            area_nm2 = cv2.countNonZero(combined_mask)*(scale_nm_per_pixel**2)
            perimeter_nm = cv2.arcLength(max(contours_crop,key=cv2.contourArea), True)*scale_nm_per_pixel

            aggregate_count += 1
            cv2.putText(output_image, str(aggregate_count), (x1, y1),
                        cv2.FONT_HERSHEY_SIMPLEX, 1.2, (0,0,255), 2, cv2.LINE_AA)

            centroid_data.append({
                'Aggregate': aggregate_count,
                'Area (nm²)': round(area_nm2,2),
                'Perimeter (nm)': round(perimeter_nm,2)
            })

        if centroid_data:
            df = pd.DataFrame(centroid_data)
            print("\n==============================")
            print(f" Results for {image_name}")
            print("==============================")
            print(df.to_string(index=False))

            excel_path = os.path.join(excel_dir, f"{os.path.splitext(image_name)[0]}.xlsx")
            df.to_excel(excel_path, index=False)

            df.to_excel(combined_writer, sheet_name=os.path.splitext(image_name)[0][:31], index=False)

            pdf_path = os.path.join(pdf_dir, f"{os.path.splitext(image_name)[0]}.pdf")
            plt.figure(figsize=(8,8))
            plt.imshow(cv2.cvtColor(output_image, cv2.COLOR_BGR2RGB))
            plt.title(image_name)
            plt.axis('off')
            plt.savefig(pdf_path)
            plt.close()

            plt.figure(figsize=(8,8))
            plt.imshow(cv2.cvtColor(output_image, cv2.COLOR_BGR2RGB))
            plt.axis('off')
            combined_pdf.savefig()
            plt.close()
        else:
            print(f" No valid aggregates detected in {image_name}")

print(f"\nCombined Excel: {combined_excel_path}")
print(f"Combined PDF: {combined_pdf_path}")