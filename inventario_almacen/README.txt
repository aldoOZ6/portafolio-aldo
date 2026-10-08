# SISTEMA DE INVENTARIO DE ALMACÉN

## Descripción
Aplicación de escritorio con interfaz gráfica en Python (Tkinter) que permite llevar control de productos en inventario, entradas, salidas y generación de reportes en Excel.

## Requisitos
- Python 3.x
- Librerías: pandas, openpyxl

## Instalación
1. Crea un entorno virtual (opcional pero recomendado)
2. Instala dependencias:
   pip install pandas openpyxl

## Archivos generados
- INVENTARIO_ACTUALIZADO.xlsx → Inventario actual
- VALES_SALIDA.xlsx → Historial de salidas con persona y fecha

## Cómo usar
1. Ejecuta `main.py`
2. Llena los campos del producto y da clic en “Agregar” o “Salida”
3. Usa los botones para buscar, guardar, y generar vales
