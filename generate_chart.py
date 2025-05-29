import matplotlib.pyplot as plt
import numpy as np

labels = ['Subtotal', 'IGV', 'Total']
values = [652, 0, 652]

plt.figure(figsize=(8, 6))
plt.bar(labels, values, color=['#f4b400', '#f4a400', '#f48000'])
plt.title('Reporte de Gastos (Totales)')
plt.xlabel('Categoría')
plt.ylabel('Monto (PEN)')
plt.grid(True, axis='y', linestyle='--', alpha=0.7)

plt.savefig('chart.png')
plt.close()