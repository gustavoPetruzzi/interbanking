# Interbanking API (BETA)
Pequeña API que genera archivos .TXT para la confeccion de transferencias y carga de nuevas cuentas a traves de un archivo .xlsx.
Por el momento, solamente soporta archivos .xlsx.

* **URL**

  /generarAltaCuenta

* **METHOD**

  `POST`
  
* **Data Params**

  **Required:**
  
  `cbu=[integer]`
  
  `length= 22`
  
  `archivo=[File .xlsx]`


