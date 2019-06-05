# Interbanking API (BETA)
Pequeña API que genera archivos .TXT para la confeccion de transferencias y carga de nuevas cuentas a traves de un archivo .xlsx.
Por el momento, soporta archivos .xlsx, .ods, .xls

* **URL**

  /generarAltaCuenta

* **METHOD**

  `POST`
  
* **Data Params**

  **Required:**
  
  `cbu=[integer]`
  
  `length= 22`
  
  `archivo=[File .xlsx]`


