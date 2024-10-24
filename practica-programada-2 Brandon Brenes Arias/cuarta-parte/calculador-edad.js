function edad() {
    const num1 = parseFloat(document.getElementById("num1").value);
    let result = "";

    if (!isNaN(num1)) { 
        if (num1 >= 18) {
            result = "Mayor de edad";
        } else {
            result = "Menor de edad";
        }
    } else {
        result = "Por favor, ingrese una edad válida.";
    }

    document.getElementById("result").innerText = result;
}
