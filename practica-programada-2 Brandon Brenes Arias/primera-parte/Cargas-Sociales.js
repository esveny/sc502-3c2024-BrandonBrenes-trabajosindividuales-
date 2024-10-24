function calculate() {
    const num1 = parseFloat(document.getElementById("num1").value);
    let result = 0;

    if (isNaN(num1)) {
        alert("Por favor, ingrese números válidos");
        return;
    }

    if (num1 >= 941001 && num1 <= 1391000) {
        result = (num1 - 941.000) * 0.10;  
    } else if (num1 > 1391001 && num1 <= 2438000) {
        result = (num1 - 1391000) * 0.15; 
    } else if (num1 > 2438001 && num1 <= 4875000) {
        result = (num1 - 2438000) * 0.20; 
    } else if (num1 > 4875001) {
        result = (num1 - 4875000) * 0.25; 
    } else {
        result = num1; 
    }

    document.getElementById("resultado").textContent = result.toFixed(2);
}
