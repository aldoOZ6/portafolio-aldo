import tkinter as tk
from tkinter import ttk, messagebox, Toplevel, simpledialog
from tkcalendar import Calendar
import pandas as pd
from datetime import datetime
import os

# ---------------- estructuras en memoria ----------------
inventario_refacciones = []
inventario_herramientas = []
historial_salidas = []
historial_entradas = []

# ---------------- CONFIG ----------------
ARCHIVO_INVENTARIO = "inventario_guardado.xlsx"  # Ahora leemos Excel
ARCHIVO_VALES = "vales_guardados.xlsx"
ARCHIVO_ENTRADAS = "entradas_guardadas.xlsx"

# Columnas estándar para cada archivo
COLS_INVENTARIO = ["Producto","Codigo","Clasificacion","Cantidad","Tipo"]
COLS_VALES = ["Producto","Codigo","Cantidad","Persona","Fecha","Tipo","Clasificacion"]
COLS_ENTRADAS = ["Producto","Cantidad","Fecha","Tipo","Clasificacion"]

# ---------- helpers para CSV/Excel ----------
def parse_cantidad(cant_str):
    if not cant_str or str(cant_str).strip() == '' or str(cant_str).strip().lower() == 'nan':
        return 0
    cant_str = str(cant_str).strip()
    try:
        return int(float(cant_str))  # Maneja floats como 1.0
    except ValueError:
        import re
        match = re.match(r'(\d+)', cant_str)
        if match:
            return int(match.group(1))
        return 0

def cargar_excel_seguro(path, columnas):
    if not os.path.exists(path) or os.path.getsize(path) == 0:
        df = pd.DataFrame(columns=columnas)
        df.to_excel(path, index=False)
        return df
    try:
        df = pd.read_excel(path, sheet_name=0)  # Primera hoja
        df = df.fillna("")
        for c in columnas:
            if c not in df.columns:
                df[c] = ""
        df = df[[c for c in columnas if c in df.columns]]
        return df
    except:
        return pd.DataFrame(columns=columnas)

def guardar_excel_seguro(df, path):
    df.to_excel(path, index=False)

# ---------- Clasificación automática ----------
CLASIFICACIONES_REF = [
    "MOTOR","AIRE","MANGUERA","NIPLES","CONEXIONES","TORNILLOS",
    "FRENOS","BALATAS","EJE","LLANTA","OTROS","ELECTRICO","PLAFON",
    "COMPRESION","UNION","SUSPENSION"
]

def determinar_clasificacion(nombre):
    if not nombre:
        return ""
    n = nombre.upper()
    for c in CLASIFICACIONES_REF:
        if c in n:
            return c
    res = simpledialog.askstring("Clasificación", f"No se pudo determinar la clasificación para '{nombre}'. Ingresa clasificación (o deja vacío):")
    return (res.strip().upper() if res else "")

# ---------- CARGAR / GUARDAR datos ----------
def cargar_datos_desde_excel():
    global inventario_refacciones, inventario_herramientas, historial_salidas, historial_entradas
    df_inv = cargar_excel_seguro(ARCHIVO_INVENTARIO, COLS_INVENTARIO)
    df_val = cargar_excel_seguro(ARCHIVO_VALES, COLS_VALES)
    df_ent = cargar_excel_seguro(ARCHIVO_ENTRADAS, COLS_ENTRADAS)

    inventario_refacciones = []
    inventario_herramientas = []

    for _, r in df_inv.iterrows():
        item = {
            'Producto': str(r.get('Producto','')),
            'Codigo': str(r.get('Codigo','')),
            'Clasificacion': str(r.get('Clasificacion','')),
            'Cantidad': parse_cantidad(r.get('Cantidad',0)),
            'Tipo': str(r.get('Tipo',''))
        }
        if item['Tipo'].upper() == 'HERRAMIENTA':
            inventario_herramientas.append(item)
        else:
            inventario_refacciones.append(item)

    historial_salidas = []
    for _, r in df_val.iterrows():
        historial_salidas.append({
            'Producto': str(r.get('Producto','')),
            'Codigo': str(r.get('Codigo','')),
            'Cantidad': parse_cantidad(r.get('Cantidad',0)),
            'Persona': str(r.get('Persona','')),
            'Fecha': str(r.get('Fecha','')),
            'Tipo': str(r.get('Tipo','')),
            'Clasificacion': str(r.get('Clasificacion',''))
        })

    historial_entradas = []
    for _, r in df_ent.iterrows():
        historial_entradas.append({
            'Producto': str(r.get('Producto','')),
            'Cantidad': parse_cantidad(r.get('Cantidad',0)),
            'Fecha': str(r.get('Fecha','')),
            'Tipo': str(r.get('Tipo','')),
            'Clasificacion': str(r.get('Clasificacion',''))
        })

def guardar_todo_a_excel():
    df_inv = pd.DataFrame(inventario_refacciones + inventario_herramientas)
    for c in COLS_INVENTARIO:
        if c not in df_inv.columns:
            df_inv[c] = ''
    guardar_excel_seguro(df_inv[COLS_INVENTARIO], ARCHIVO_INVENTARIO)

    df_val = pd.DataFrame(historial_salidas)
    for c in COLS_VALES:
        if c not in df_val.columns:
            df_val[c] = ''
    guardar_excel_seguro(df_val[COLS_VALES], ARCHIVO_VALES)

    df_ent = pd.DataFrame(historial_entradas)
    for c in COLS_ENTRADAS:
        if c not in df_ent.columns:
            df_ent[c] = ''
    guardar_excel_seguro(df_ent[COLS_ENTRADAS], ARCHIVO_ENTRADAS)

    messagebox.showinfo('Guardado', 'Datos guardados correctamente.')

# ---------- Funciones para GUI ----------
def obtener_lista_productos():
    return [item['Producto'] for item in inventario_refacciones + inventario_herramientas if item.get('Producto')]

def actualizar_tabla_inventario(filtro_texto=''):
    tree_ref.delete(*tree_ref.get_children())
    tree_her.delete(*tree_her.get_children())

    for item in inventario_refacciones:
        if filtro_texto and filtro_texto.lower() not in item['Producto'].lower():
            continue
        vals_ref = (item['Producto'], item.get('Clasificacion',''), item.get('Cantidad',0))
        tree_ref.insert('', 'end', values=vals_ref)

    for item in inventario_herramientas:
        if filtro_texto and filtro_texto.lower() not in item['Producto'].lower():
            continue
        vals_her = (item['Producto'], item.get('Clasificacion',''), item.get('Cantidad',0))
        tree_her.insert('', 'end', values=vals_her)

def actualizar_entradas_vistas():
    entrada_tree_ref.delete(*entrada_tree_ref.get_children())
    entrada_tree_her.delete(*entrada_tree_her.get_children())
    for e in historial_entradas:
        vals_ref = (e.get('Producto',''), e.get('Cantidad',0), e.get('Fecha',''), e.get('Clasificacion',''))
        vals_her = (e.get('Producto',''), e.get('Cantidad',0), e.get('Fecha',''), e.get('Clasificacion',''))
        if e.get('Tipo','').upper() == 'HERRAMIENTA':
            entrada_tree_her.insert('', 'end', values=vals_her)
        else:
            entrada_tree_ref.insert('', 'end', values=vals_ref)

def actualizar_historial_vistas():
    vales_text.delete('1.0', tk.END)
    for i, salida in enumerate(historial_salidas, start=1):
        fecha = salida.get('Fecha','')
        try:
            fecha_dt = datetime.strptime(fecha, '%Y-%m-%d')
            fecha_str = f"{fecha_dt.day}/{fecha_dt.month}/{fecha_dt.year}"
        except Exception:
            fecha_str = fecha
        codigo = salida.get('Codigo','')  # <--- Código ingresado se muestra
        vale = f"VALE #{i} - {fecha_str}\nCódigo: {codigo}\nA: {salida.get('Persona','')}\nProducto: {salida.get('Producto','')}  CANTIDAD: {salida.get('Cantidad',0)}\nTIPO: {salida.get('Tipo','')}  CLASIFICACION: {salida.get('Clasificacion','')}\n" + ("="*60) + "\n\n"
        vales_text.insert(tk.END, vale)

# ---------- Operaciones de inventario ----------
def agregar_producto_entrada():
    producto = entry_producto.get().strip()
    cantidad_text = entry_cantidad_ent.get().strip()
    tipo = tipo_var.get().strip()
    fecha = entry_fecha_ent.get().strip()
    clas = entry_clasificacion_ent.get().strip()

    if not producto or not cantidad_text.isdigit() or not tipo:
        messagebox.showwarning('Campos incompletos', 'Completa todos los campos correctamente.')
        return
    cantidad = int(cantidad_text)
    if not clas and tipo.upper() == 'REFACCION':
        clas = determinar_clasificacion(producto)

    inventario_lista = inventario_herramientas if tipo.upper()=='HERRAMIENTA' else inventario_refacciones

    encontrado = None
    for item in inventario_lista:
        if item['Producto'].lower() == producto.lower():
            encontrado = item
            break

    if encontrado:
        encontrado['Cantidad'] += cantidad
        if not encontrado.get('Clasificacion') and clas:
            encontrado['Clasificacion'] = clas
    else:
        inventario_lista.append({
            'Producto': producto,
            'Codigo': '',
            'Clasificacion': clas,
            'Cantidad': cantidad,
            'Tipo': tipo
        })

    historial_entradas.append({
        'Producto': producto,
        'Cantidad': cantidad,
        'Fecha': fecha if fecha else datetime.today().strftime('%Y-%m-%d'),
        'Tipo': tipo,
        'Clasificacion': clas
    })
    actualizar_tabla_inventario(filtro_var.get())
    actualizar_entradas_vistas()
    limpiar_campos_entrada()

def limpiar_campos_entrada():
    entry_producto.delete(0, tk.END)
    entry_cantidad_ent.delete(0, tk.END)
    tipo_var.set('')
    entry_fecha_ent.delete(0, tk.END)
    entry_clasificacion_ent.delete(0, tk.END)

# ---------- Funciones de Salida (corregido para código) ----------
def registrar_salida_producto():
    producto = combo_producto_salida.get().strip()
    codigo = entry_codigo_salida.get().strip()  # Se captura el código ingresado
    cantidad_text = entry_cantidad_salida.get().strip()
    persona = entry_persona.get().strip()
    fecha_input = entry_fecha_salida.get().strip()
    tipo = tipo_salida_var.get().strip()

    if not producto and not codigo:
        messagebox.showwarning('Campos incompletos', 'Selecciona un producto o ingresa un código.')
        return
    if not cantidad_text.isdigit() or not persona:
        messagebox.showwarning('Campos incompletos', 'Completa todos los campos de salida.')
        return
    cantidad = int(cantidad_text)
    fecha = datetime.today().strftime('%Y-%m-%d')
    if fecha_input:
        try:
            fecha_dt = datetime.strptime(fecha_input, '%d/%m/%Y')
            fecha = fecha_dt.strftime('%Y-%m-%d')
        except ValueError:
            messagebox.showerror('Fecha inválida', 'La fecha debe estar en formato DD/MM/AAAA.')
            return

    listas_busqueda = []
    if tipo:
        listas_busqueda = [inventario_herramientas] if tipo.upper()=='HERRAMIENTA' else [inventario_refacciones]
    else:
        listas_busqueda = [inventario_herramientas, inventario_refacciones]

    encontrado = None
    for lista in listas_busqueda:
        for item in lista:
            if codigo and str(item.get('Codigo','')).strip().lower() == codigo.lower():
                encontrado = item
                break
            if producto and item['Producto'].lower() == producto.lower():
                encontrado = item
                break
        if encontrado:
            break

    if not encontrado:
        messagebox.showerror('No encontrado', 'Producto o código no existe en inventario.')
        return

    stock = int(encontrado.get('Cantidad',0))
    if stock < cantidad:
        messagebox.showerror('Stock insuficiente', f"Solo hay {stock} unidades.")
        return
    encontrado['Cantidad'] = stock - cantidad
    clas = encontrado.get('Clasificacion','') or (determinar_clasificacion(encontrado.get('Producto','')) if (tipo and tipo.upper()=='REFACCION') or (not tipo and encontrado.get('Tipo','').upper()=='REFACCION') else '')

    historial_salidas.append({
        'Producto': encontrado.get('Producto',''),
        'Codigo': codigo if codigo else encontrado.get('Codigo',''),  # aquí se guarda el código ingresado
        'Cantidad': cantidad,
        'Persona': persona,
        'Fecha': fecha,
        'Tipo': encontrado.get('Tipo','') if encontrado.get('Tipo','') else tipo,
        'Clasificacion': clas
    })

    actualizar_tabla_inventario(filtro_var.get())
    actualizar_historial_vistas()
    limpiar_campos_salida()

def limpiar_campos_salida():
    combo_producto_salida.set('')
    entry_codigo_salida.delete(0, tk.END)
    entry_cantidad_salida.delete(0, tk.END)
    entry_persona.delete(0, tk.END)
    entry_fecha_salida.delete(0, tk.END)
    tipo_salida_var.set('')

# ---------- Eliminaciones ----------
def eliminar_producto_seleccionado():
    sel = None
    s = tree_ref.selection()
    if s:
        sel = ('ref', s[0])
    else:
        s2 = tree_her.selection()
        if s2:
            sel = ('her', s2[0])
    if not sel:
        messagebox.showwarning('Selecciona un producto', 'Debes seleccionar un producto para eliminar.')
        return
    if not messagebox.askyesno('Eliminar', '¿Eliminar este producto del inventario?'):
        return
    vals = tree_ref.item(sel[1])['values'] if sel[0]=='ref' else tree_her.item(sel[1])['values']
    prod = vals[0]
    global inventario_refacciones, inventario_herramientas
    if sel[0]=='ref':
        inventario_refacciones = [p for p in inventario_refacciones if p['Producto'] != prod]
    else:
        inventario_herramientas = [p for p in inventario_herramientas if p['Producto'] != prod]
    actualizar_tabla_inventario(filtro_var.get())

def eliminar_ultima_salida():
    if not historial_salidas:
        messagebox.showwarning('Sin salidas', 'No hay salidas para eliminar.')
        return
    if messagebox.askyesno('Eliminar', '¿Eliminar la última salida?'):
        ultima = historial_salidas.pop()
        prod = ultima.get('Producto','')
        cant = int(ultima.get('Cantidad',0))
        tipo = ultima.get('Tipo','').upper()
        inventario_lista = inventario_herramientas if tipo=='HERRAMIENTA' else inventario_refacciones
        for item in inventario_lista:
            if item['Producto'] == prod:
                item['Cantidad'] += cant
                break
        actualizar_tabla_inventario(filtro_var.get())
        actualizar_historial_vistas()

def eliminar_entrada_seleccionada():
    sel = entrada_tree_ref.selection() or entrada_tree_her.selection()
    if not sel:
        messagebox.showwarning('Selecciona entrada', 'Debes seleccionar una entrada para eliminar.')
        return
    if not messagebox.askyesno('Eliminar', '¿Eliminar esta entrada?'):
        return
    tree_used = entrada_tree_ref if entrada_tree_ref.selection() else entrada_tree_her
    v = tree_used.item(sel[0])['values']
    prod = v[0]
    historial_entradas[:] = [e for e in historial_entradas if e['Producto'] != prod]

    lista_inv = inventario_herramientas if tree_used==entrada_tree_her else inventario_refacciones
    for item in lista_inv:
        if item['Producto'] == prod:
            item['Cantidad'] -= int(v[1])
            if item['Cantidad'] < 0:
                item['Cantidad'] = 0

    actualizar_tabla_inventario(filtro_var.get())
    actualizar_entradas_vistas()

# ---------- Calendarios ----------
def abrir_calendario(entry_widget):
    def seleccionar_fecha():
        fecha = calendario.get_date()
        entry_widget.delete(0, tk.END)
        entry_widget.insert(0, fecha)
        cal_win.destroy()
    cal_win = Toplevel(root)
    cal_win.title('Selecciona fecha')
    calendario = Calendar(cal_win, date_pattern='dd/mm/yyyy', locale='es_ES')
    calendario.pack(padx=10, pady=10)
    tk.Button(cal_win, text='Seleccionar', command=seleccionar_fecha).pack(pady=5)

# ---------- INICIALIZACIÓN GUI ----------
root = tk.Tk()
root.title('Sistema de Inventario Mejorado')
root.geometry('1100x800')
root.configure(bg='#f5f5f5')

# ---------- TOP BOTONES ----------
frame_botones = tk.Frame(root, bg='#eeeeee', pady=10)
frame_botones.pack(fill='x')
btn_guardar = tk.Button(frame_botones, text='💾 Guardar Datos', command=guardar_todo_a_excel, bg='#2196f3', fg='white', font=('Arial', 12, 'bold'), padx=20, pady=5)
btn_guardar.pack(side='left', padx=20)
btn_cerrar = tk.Button(frame_botones, text='❌ Cerrar', command=lambda: (guardar_todo_a_excel() if messagebox.askyesno("Salir","¿Guardar antes de salir?") else None, root.destroy()), bg='#e53935', fg='white', font=('Arial', 12, 'bold'), padx=20, pady=5)
btn_cerrar.pack(side='right', padx=20)

# ---------- NOTEBOOK ----------
notebook = ttk.Notebook(root)
tab_inv = tk.Frame(notebook)
tab_ent = tk.Frame(notebook)
tab_sal = tk.Frame(notebook)
notebook.add(tab_inv, text='Inventario')
notebook.add(tab_ent, text='Entrada')
notebook.add(tab_sal, text='Salida')
notebook.pack(fill='both', expand=True)

# ---------- STYLE ----------
style = ttk.Style()
style.theme_use('default')
style.configure('Treeview', background='#f0f0f0', foreground='black', rowheight=24, fieldbackground='#f0f0f0')
style.map('Treeview', background=[('selected', '#347083')])
style.configure('Treeview.Heading', font=('Arial', 11, 'bold'))

# ---------- INVENTARIO TAB ----------
frame_inv = tk.LabelFrame(tab_inv, text='Inventario', font=('Arial', 12, 'bold'))
frame_inv.pack(fill='both', expand=True, padx=20, pady=10)

search_frame = tk.Frame(frame_inv)
search_frame.pack(fill='x', padx=5, pady=5)
filtro_var = tk.StringVar()
entry_filtro = tk.Entry(search_frame, textvariable=filtro_var)
entry_filtro.pack(side='left', padx=5)
def filtrar_inventario(*args):
    actualizar_tabla_inventario(filtro_var.get())
filtro_var.trace('w', filtrar_inventario)

tree_ref = ttk.Treeview(frame_inv, columns=("Producto","Clasificacion","Cantidad"), show='headings')
tree_ref.heading("Producto", text="Producto")
tree_ref.heading("Clasificacion", text="Clasificación")
tree_ref.heading("Cantidad", text="Cantidad")
tree_ref.pack(fill='both', expand=True, padx=5, pady=5)

tree_her = ttk.Treeview(frame_inv, columns=("Producto","Clasificacion","Cantidad"), show='headings')
tree_her.heading("Producto", text="Producto")
tree_her.heading("Clasificacion", text="Clasificación")
tree_her.heading("Cantidad", text="Cantidad")
tree_her.pack(fill='both', expand=True, padx=5, pady=5)

btn_elim_prod = tk.Button(frame_inv, text='Eliminar Producto', command=eliminar_producto_seleccionado, bg='#f44336', fg='white', font=('Arial', 11, 'bold'))
btn_elim_prod.pack(pady=5)

# ---------- ENTRADA TAB ----------
frame_ent_form = tk.LabelFrame(tab_ent, text='Registrar Entrada', font=('Arial', 12, 'bold'))
frame_ent_form.pack(fill='x', padx=20, pady=10)

tk.Label(frame_ent_form, text='Producto:').grid(row=0, column=0, padx=5, pady=5)
entry_producto = tk.Entry(frame_ent_form)
entry_producto.grid(row=0, column=1, padx=5, pady=5)

tk.Label(frame_ent_form, text='Cantidad:').grid(row=1, column=0, padx=5, pady=5)
entry_cantidad_ent = tk.Entry(frame_ent_form)
entry_cantidad_ent.grid(row=1, column=1, padx=5, pady=5)

tk.Label(frame_ent_form, text='Tipo:').grid(row=1, column=2, padx=5, pady=5)
tipo_var = tk.StringVar()
combo_tipo_ent = ttk.Combobox(frame_ent_form, textvariable=tipo_var, values=['Refaccion','Herramienta'], state='readonly')
combo_tipo_ent.grid(row=1, column=3, padx=5, pady=5)

tk.Label(frame_ent_form, text='Clasificación:').grid(row=2, column=0, padx=5, pady=5)
entry_clasificacion_ent = tk.Entry(frame_ent_form)
entry_clasificacion_ent.grid(row=2, column=1, padx=5, pady=5)

tk.Label(frame_ent_form, text='Fecha (dd/mm/aaaa):').grid(row=3, column=0, padx=5, pady=5)
entry_fecha_ent = tk.Entry(frame_ent_form)
entry_fecha_ent.grid(row=3, column=1, padx=5, pady=5)
tk.Button(frame_ent_form, text='📅', command=lambda: abrir_calendario(entry_fecha_ent)).grid(row=3, column=2, padx=5)

tk.Button(frame_ent_form, text='Registrar Entrada', command=agregar_producto_entrada, bg='#4caf50', fg='white').grid(row=4, column=0, columnspan=2, pady=10)
btn_elim_ent = tk.Button(frame_ent_form, text='Eliminar Entrada', command=eliminar_entrada_seleccionada, bg='#f44336', fg='white')
btn_elim_ent.grid(row=4, column=2, columnspan=2, pady=10)

entrada_tree_ref = ttk.Treeview(tab_ent, columns=("Producto","Cantidad","Fecha","Clasificacion"), show='headings')
entrada_tree_ref.heading("Producto", text="Producto")
entrada_tree_ref.heading("Cantidad", text="Cantidad")
entrada_tree_ref.heading("Fecha", text="Fecha")
entrada_tree_ref.heading("Clasificacion", text="Clasificación")
entrada_tree_ref.pack(fill='both', expand=True, padx=10, pady=5)

entrada_tree_her = ttk.Treeview(tab_ent, columns=("Producto","Cantidad","Fecha","Clasificacion"), show='headings')
entrada_tree_her.heading("Producto", text="Producto")
entrada_tree_her.heading("Cantidad", text="Cantidad")
entrada_tree_her.heading("Fecha", text="Fecha")
entrada_tree_her.heading("Clasificacion", text="Clasificación")
entrada_tree_her.pack(fill='both', expand=True, padx=10, pady=5)

# ---------- SALIDA TAB ----------
frame_sal_form = tk.LabelFrame(tab_sal, text='Registrar Salida', font=('Arial', 12, 'bold'))
frame_sal_form.pack(fill='x', padx=20, pady=10)

tk.Label(frame_sal_form, text='Código:').grid(row=0, column=0, padx=5, pady=5)
entry_codigo_salida = tk.Entry(frame_sal_form)
entry_codigo_salida.grid(row=0, column=1, padx=5, pady=5)

tk.Label(frame_sal_form, text='Producto:').grid(row=1, column=0, padx=5, pady=5)
combo_producto_salida = ttk.Combobox(frame_sal_form, values=obtener_lista_productos())
combo_producto_salida.grid(row=1, column=1, padx=5, pady=5)

tk.Label(frame_sal_form, text='Cantidad:').grid(row=2, column=0, padx=5, pady=5)
entry_cantidad_salida = tk.Entry(frame_sal_form)
entry_cantidad_salida.grid(row=2, column=1, padx=5, pady=5)

tk.Label(frame_sal_form, text='Persona:').grid(row=3, column=0, padx=5, pady=5)
entry_persona = tk.Entry(frame_sal_form)
entry_persona.grid(row=3, column=1, padx=5, pady=5)

tk.Label(frame_sal_form, text='Fecha (dd/mm/aaaa):').grid(row=4, column=0, padx=5, pady=5)
entry_fecha_salida = tk.Entry(frame_sal_form)
entry_fecha_salida.grid(row=4, column=1, padx=5, pady=5)
tk.Button(frame_sal_form, text='📅', command=lambda: abrir_calendario(entry_fecha_salida)).grid(row=4, column=2, padx=5)

tk.Label(frame_sal_form, text='Tipo:').grid(row=5, column=0, padx=5, pady=5)
tipo_salida_var = tk.StringVar()
combo_tipo_salida = ttk.Combobox(frame_sal_form, textvariable=tipo_salida_var, values=['Refaccion','Herramienta'], state='readonly')
combo_tipo_salida.grid(row=5, column=1, padx=5, pady=5)

tk.Button(frame_sal_form, text='Registrar Salida', command=registrar_salida_producto, bg='#ff9800', fg='white').grid(row=6, column=0, columnspan=2, pady=10)
btn_elim_vale = tk.Button(frame_sal_form, text='Eliminar Última Salida', command=eliminar_ultima_salida, bg='#f44336', fg='white')
btn_elim_vale.grid(row=6, column=2, columnspan=2, pady=10)

vales_text = tk.Text(tab_sal, height=20)
vales_text.pack(fill='both', expand=True, padx=10, pady=5)

cargar_datos_desde_excel()
actualizar_tabla_inventario()
actualizar_entradas_vistas()
actualizar_historial_vistas()
combo_producto_salida['values'] = obtener_lista_productos()

root.mainloop()
