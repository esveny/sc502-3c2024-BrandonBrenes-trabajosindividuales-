function imprimir() {
    // Datos del objeto y las notas
    const person = { name: "Brandon", lastName : "Brenes", age: 19 };
    let notas = [80, 60, 92, 47, 98];
    
    // Agregar una nota al arreglo
    notas.push(88);

    // Calcular el promedio
    let suma = 0;
    for (let i = 0; i < notas.length; i++) {
        suma += notas[i];
    }
    let promedio = suma / notas.length;

    // Recorrer el arreglo de notas e imprimir cada una
    let resultado = `<br>Nombre: ${person.name}
    <br>Apellido : ${person.lastName}
    <br>Notas: `;
    for (let i = 0; i < notas.length; i++) {
        resultado += notas[i] + " ";
    }

    resultado += `<br>Promedio: ${promedio.toFixed(2)}`;

    // Mostrar resultado en la página
    document.getElementById("result").innerHTML = resultado;
}
