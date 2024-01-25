const url = "http://localhost:8080/server/Practica16/apirest/";
let token = "";
let isZonaAdmin = false;

window.onload = () => {
    if (getCookie("token") !== "") {
        token = getCookie("token");
        document.getElementById("login").style.display = "none";
        document.getElementById("principal").style.display = "flex";
        getCategorias();
    }
    document.getElementsByTagName("form")[0].onsubmit = () => {
        login();
        return false;
    }
    document.getElementById("home").onclick = () => {
        getCategorias();
        return false;
    }
    document.getElementById("carrito").onclick = () => {
        cargarCarrito();
        return false;
    }
    document.getElementById("zonaAdmin").onclick = () => {
        zonaAdmin();
        return false;
    }
    document.getElementById("cerrarSesion").onclick = () => {
        cerrarSesion();
        return false;
    }
    document.getElementById("logo").onclick = () => {
        getCategorias();
        return false;
    }

}

// Login y logout
const login = async () => {
    if (isZonaAdmin) {
        const user = document.getElementById("usuario").value;
        const pass = document.getElementById("clave").value;

        if (user !== "admin" || pass !== "admin") {
            alert("Usuario o contraseña incorrectos.");
            return false;
        }

        document.getElementById("login").style.display = "none";
        document.getElementById("principal").style.display = "flex";

        cargarZonaAdmin();
        document.title = "Zona admin";
        setCookie("admin", true);
        return;
    }
    if (document.getElementById("msgError") !== null) { document.getElementById("msgError").parentElement.removeChild(document.getElementById("msgError")); }
    if (document.getElementById("usuario").value === "" || document.getElementById("clave").value === "") {
        const msgError = document.createElement("p");
        msgError.innerText = "Debe completar todos los campos.";
        msgError.style.color = "red";
        msgError.style.fontSize = "12px";
        msgError.style.marginTop = "5px";
        msgError.style.marginBottom = "0px";
        msgError.id = "msgError";
        document.getElementsByTagName("form")[0].appendChild(msgError);
        return false;
    }

    const bodyContent = new FormData();
    bodyContent.append("correo", document.getElementById("usuario").value);
    bodyContent.append("password", document.getElementById("clave").value);

    const response = await fetch(url + "login", {
        method: "POST",
        body: bodyContent,
        headers: {
            "Accept": "*/*"
        }
    });
    if (response.status === 400) {
        if (document.getElementById("msgError") !== null) { document.getElementById("msgError").parentElement.removeChild(document.getElementById("msgError")); }
        const msgError = document.createElement("p");
        msgError.innerText = "Usuario o contraseña incorrectos.";
        msgError.style.color = "red";
        msgError.style.fontSize = "12px";
        msgError.style.marginTop = "5px";
        msgError.style.marginBottom = "0px";
        msgError.id = "msgError";
        document.getElementsByTagName("form")[0].appendChild(msgError);
    }

    if (response.status !== 200) {
        return false;
    }

    token = await response.text();
    token = token.replace(/['"]+/g, '');

    document.getElementById("login").style.display = "none";
    document.getElementById("principal").style.display = "flex";

    setCookie("token", token);
    document.title = "Tienda online";
    getCategorias();

    return false;
}
const cerrarSesion = () => {
    const elementsToRemove = ["token", "carrito", "admin"];
    elementsToRemove.forEach(element => removeCookie(element));

    isZonaAdmin = false;

    const loginElement = document.getElementById("login");
    const principalElement = document.getElementById("principal");
    const divCategoriasElement = document.getElementById("divCategorias");
    const tablaElement = document.getElementById("tabla");

    if (loginElement !== null) loginElement.style.display = "flex";
    if (principalElement !== null) principalElement.style.display = "none";

    document.title = "Formulario de login";

    if (divCategoriasElement !== null) { divCategoriasElement.parentElement.removeChild(divCategoriasElement) };
    if (tablaElement !== null) { tablaElement.parentElement.removeChild(tablaElement) };


    if (document.getElementById("msgError") !== null) { document.getElementById("msgError").parentElement.removeChild(document.getElementById("msgError")); }

    document.getElementById("usuario").value = "";
    document.getElementById("clave").value = "";
    return false;
}

// Categorias
const getCategorias = async () => {
    document.title = "Tienda online";
    limpiarPagina();

    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }

    const response = await fetch(url + "categoria", {
        method: "GET",
        headers: headersList
    });

    if (response.status !== 200) {
        return false;
    }
    const categorias = await response.json();

    const div = document.createElement("div");
    div.id = "divCategorias";
    div.classList.add("d-flex");

    categorias.forEach(categoria => {

        const card = document.createElement("div");
        card.classList.add("card");
        card.classList.add("m-3");
        card.style.width = "15rem";

        const img = document.createElement("img");
        img.classList.add("card-img-top");
        img.style.minHeight = "238.18px";
        img.src = categoria.img_url;

        const body = document.createElement("div");
        body.classList.add("card-body");

        const h5 = document.createElement("h5");
        h5.classList.add("card-title");
        h5.innerText = categoria.nombre;

        const p = document.createElement("p");
        p.classList.add("card-text");
        p.innerText = categoria.descripcion;

        const a = document.createElement("a");
        a.classList.add("btn");
        a.classList.add("btn-outline-dark");
        a.href = "#";
        a.innerHTML = "Ver productos";
        a.onclick = () => {
            getProductosBy(categoria.codCat);
        }

        body.appendChild(h5);
        body.appendChild(p);
        body.appendChild(a);

        card.appendChild(img);
        card.appendChild(body);

        div.appendChild(card);
    });
    document.getElementById("principal").appendChild(div);
    return false;
};

// Productos
const getProductosBy = async (id) => {
    document.title = "Tienda online";

    limpiarPagina();

    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }

    const response = await fetch(url + "categoria/" + id, {
        method: "GET",
        headers: headersList
    });

    if (response.status !== 200) { return false; }

    let carrito = [];
    if (getCookie("carrito") !== '') { carrito = JSON.parse(getCookie("carrito")); }

    const productos = await response.json();

    const headers = ["Nombre", "Descripción", "Peso", "Stock", "Comprar"];

    const table = document.createElement("table");
    table.id = "tabla";
    table.classList.add("table");
    let thead = document.createElement("thead");
    let tbody = document.createElement("tbody");
    let tr = document.createElement("tr");
    headers.forEach(header => {
        let th = document.createElement("th");
        th.innerText = header;
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    if (productos.length === 0) {
        tr = document.createElement("tr");
        td = document.createElement("td");
        td.innerText = "No hay productos en esta categoría.";
        td.colSpan = 5;
        tr.appendChild(td);
        tbody.appendChild(tr);
    }
    productos.forEach(row => {
        let tr = document.createElement("tr");
        tr.id = row.codProd;
        let td = document.createElement("td");
        td.innerText = row.nombre;
        tr.appendChild(td);

        td = document.createElement("td");
        td.innerText = row.descripción;
        tr.appendChild(td);

        td = document.createElement("td");
        td.innerText = row.peso;
        tr.appendChild(td);

        td = document.createElement("td");


        let disabled = false;
        if (carrito.find(c => row.codProd === c.codProd) === undefined) {
            td.innerText = row.stock;

        } else {
            const aux = carrito.find(c => row.codProd === c.codProd);
            td.innerText = row.stock - aux.cantidad;
            if (row.stock - aux.cantidad <= 0) {
                disabled = true;

            }

        }
        tr.appendChild(td);

        td = document.createElement("td");

        const div = document.createElement("div");
        div.classList.add("input-group");
        div.classList.add("mb-3");

        const inputCantidad = document.createElement("input");
        inputCantidad.classList.add("form-control");
        inputCantidad.type = "number";

        const btn = document.createElement("button");
        btn.classList.add("btn");
        btn.classList.add("btn-outline-dark");
        btn.innerText = "Comprar";

        btn.onclick = () => {
            comprar(row.codProd);
        }
        btn.disabled = disabled;
        div.appendChild(inputCantidad);
        div.appendChild(btn);

        td.appendChild(div);
        tr.appendChild(td);

        tbody.appendChild(tr);
    });

    table.appendChild(tbody);

    const div = document.createElement("div");
    div.classList.add("d-flex");
    div.classList.add("justify-content-center");
    div.id = "divCategorias";
    div.appendChild(table);

    document.getElementById("principal").appendChild(div);

}

// Acción de comprar producto
const comprar = async (id) => {
    showLoader();
    let carrito = [];
    if (getCookie("carrito") !== '') {
        carrito = JSON.parse(getCookie("carrito"));
    }
    const producto = await getProductoById(id);
    if (!producto) {
        alert("No se pudo obtener el producto.");
        return;
    }

    const inputCantidad = parseInt(document.getElementById(id).children[4].children[0].children[0].value);
    const stock = parseInt(document.getElementById(id).children[3].innerText);

    if (inputCantidad <= 0 || isNaN(inputCantidad)) {
        hideLoader();
        alert("La cantidad debe ser mayor a 0.");
        return;
    }

    if (inputCantidad > stock) {
        hideLoader();
        alert("No hay suficiente stock, solo hay " + stock + " unidades.");
        return;
    }

    const prodCarrito = carrito.find(row => row.codProd === id);

    if (prodCarrito !== undefined) {
        prodCarrito.cantidad = parseInt(prodCarrito.cantidad) + parseInt(inputCantidad);
        setCookie("carrito", JSON.stringify(carrito));
        getProductosBy(producto.codCat);
        hideLoader();
        return;
    }

    carrito.push({
        codProd: id,
        cantidad: inputCantidad
    });


    setCookie("carrito", JSON.stringify(carrito));
    getProductosBy(producto.codCat);
    hideLoader();

};

// Carrito
const cargarCarrito = async () => {
    showLoader();
    let carrito = [];
    document.title = "Tienda online";
    if (getCookie("carrito") !== '') {
        carrito = JSON.parse(getCookie("carrito"));
    }

    limpiarPagina();

    const headers = ["Nombre", "Descripción", "Peso", "Cantidad"];


    let table = document.createElement("table");
    table.id = "tabla";
    let thead = document.createElement("thead");
    let tbody = document.createElement("tbody");
    let tr = document.createElement("tr");
    headers.forEach(header => {
        let th = document.createElement("th");
        th.innerText = header;
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);
    if (carrito.length === 0) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.innerText = "No hay productos en el carrito.";
        td.colSpan = 4;
        tr.appendChild(td);
        tbody.appendChild(tr);
        table.appendChild(tbody);

        const div = document.createElement("div");
        div.id = "divCarrito";
        div.classList.add("d-flex");
        div.classList.add("justify-content-center");
        div.appendChild(table);

        document.getElementById("principal").appendChild(div);
        hideLoader();
        return;
    }
    carrito.forEach(async row => {

        const producto = await getProductoById(row.codProd);
        let tr = document.createElement("tr");
        tr.id = row.codProd;

        // Nombre
        let td = document.createElement("td");
        td.innerText = producto.nombre;
        tr.appendChild(td);

        // Descripción
        td = document.createElement("td");
        td.innerText = producto.descripción;
        tr.appendChild(td);

        // Peso
        td = document.createElement("td");
        td.innerText = producto.peso;
        tr.appendChild(td);

        // Cantidad
        const div = document.createElement("div");
        div.classList.add("input-group");
        div.classList.add("mb-3");

        const inputCantidad = document.createElement("input");
        inputCantidad.classList.add("form-control");
        inputCantidad.type = "number";
        inputCantidad.value = row.cantidad;

        const btn = document.createElement("button");
        btn.classList.add("btn");
        btn.classList.add("btn-outline-dark");
        btn.innerText = "Actualizar";
        btn.onclick = () => {
            actualizar(row.codProd);
        }
        div.appendChild(inputCantidad);
        div.appendChild(btn);
        td = document.createElement("td");
        td.appendChild(div);
        tr.appendChild(td);

        // Fila
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);

    const div = document.createElement("div");
    div.id = "divCarrito";
    div.classList.add("d-flex");
    div.classList.add("justify-content-center");
    div.appendChild(table);

    hideLoader();
    document.getElementById("principal").appendChild(div);

}

// Obtener producto por id
const getProductoById = async (id) => {
    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }

    const response = await fetch(url + "producto/" + id, {
        method: "GET",
        headers: headersList
    });

    if (response.status !== 200) {
        return false;
    }
    const producto = await response.json();

    return producto;
};

// Actualizar carrito
const actualizar = async (id) => {
    let carrito = JSON.parse(getCookie("carrito"));
    const inputCantidad = document.getElementById(id).children[3].children[0].children[0].value;

    const producto = await getProductoById(id);
    if (inputCantidad > producto.stock) {
        alert("No hay suficiente stock, solo hay " + producto.stock + " unidades.");
        return;
    }
    const prodCarrito = carrito.find(row => row.codProd === id);
    prodCarrito.cantidad = inputCantidad;
    if (inputCantidad <= 0) {
        carrito.splice(carrito.indexOf(prodCarrito), 1);
    }
    setCookie("carrito", JSON.stringify(carrito));
    cargarCarrito();
};

// Zona admin
const zonaAdmin = () => {
    if (getCookie("admin") !== "true") {
        document.getElementById("login").style.display = "flex";

        if (document.getElementById("msgError") !== null) { document.getElementById("msgError").parentElement.removeChild(document.getElementById("msgError")); }

        document.getElementById("usuario").value = "";
        document.getElementById("clave").value = "";

        document.getElementById("principal").style.display = "none";
        isZonaAdmin = true;
        return false;
    }
    document.title = "Zona admin";
    cargarZonaAdmin();

    return false;
}

const cargarZonaAdmin = async () => {
    showLoader();

    limpiarPagina();

    // Obtener categorías
    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }
    const response = await fetch(url + "categoria", {
        method: "GET",
        headers: headersList
    });


    if (response.status !== 200) {
        hideLoader();
        return false;
    }
    const categorias = await response.json();

    const div = document.createElement("div");
    div.id = "divCategorias";

    div.classList.add("d-flex");
    div.classList.add("justify-content-center");
    div.classList.add("align-items-center");
    div.classList.add("flex-column");
    const select = document.createElement("select");
    select.classList.add("form-select");
    select.classList.add("mb-3");
    select.classList.add("w-50");
    select.id = "selectCategoria";
    categorias.forEach(categoria => {
        const option = document.createElement("option");
        option.value = categoria.codCat;
        option.innerText = categoria.nombre;
        select.appendChild(option);
    });
    select.onchange = () => {
        getProductosByAdmin(select.value);
    }

    getProductosByAdmin(select.value);
    div.appendChild(select);
    document.getElementById("principal").appendChild(div);
    hideLoader();

};

const getProductosByAdmin = async (id) => {


    limpiarPagina();
    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }
    let response = await fetch(url + "categoria/" + id, {
        method: "GET",
        headers: headersList
    });

    if (response.status !== 200) {
        return false;
    }
    const productos = await response.json();

    const headers = ["Nombre", "Acciones"];
    const table = document.createElement("table");
    table.id = "tabla";
    const thead = document.createElement("thead");
    const tbody = document.createElement("tbody");
    let tr = document.createElement("tr");
    headers.forEach(header => {
        const th = document.createElement("th");
        th.innerText = header;
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);
    if (productos.length === 0) {
        tr = document.createElement("tr");
        const td = document.createElement("td");
        td.innerText = "No hay productos en esta categoría.";
        td.colSpan = 2;
        tr.appendChild(td);
        tbody.appendChild(tr);

    }
    productos.forEach(row => {
        const tr = document.createElement("tr");
        tr.id = row.codProd;
        let td = document.createElement("td");
        td.innerText = row.nombre;
        tr.appendChild(td);

        td = document.createElement("td");
        const img = document.createElement("img");
        img.src = "https://cdn-icons-png.flaticon.com/512/2541/2541991.png";


        img.onclick = () => {
            mostaraStock(row.codProd);
        }
        td.appendChild(img);


        tr.appendChild(td);
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    document.getElementById("divCategorias").appendChild(table);
};

// Stock
const mostaraStock = async (id) => {
    document.querySelectorAll("tr").forEach(tr => {
        if (tr.children[2] !== undefined) {
            tr.children[2].parentElement.removeChild(tr.children[2]);
        }
    });

    if (document.getElementById(id).children[2] !== undefined) {
        return;
    }
    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }
    let response = await fetch(url + "producto/" + id, {
        method: "GET",
        headers: headersList
    });

    if (response.status !== 200) {
        return false;
    }
    const producto = await response.json();
    const td = document.createElement("td");
    const input = document.createElement("input");
    input.type = "number";
    input.value = producto.stock;
    const btn = document.createElement("button");
    btn.classList.add("btn");
    btn.classList.add("btn-outline-dark");
    btn.innerText = "Actualizar";
    btn.onclick = () => {
        actualizarStock(id);
    }
    td.appendChild(input);
    td.appendChild(btn);
    td.style.position = "absolute";
    document.getElementById(id).appendChild(td);

}

const actualizarStock = async (id) => {

    let headersList = {
        "Accept": "*/*",
        "Authorization": "Bearer " + token
    }
    const body = {
        stock: parseInt(document.getElementById(id).children[2].children[0].value)
    }

    const response = await fetch(url + "producto/actualizarStock/" + id, {
        method: "PUT",
        headers: headersList,
        body: JSON.stringify(body)
    });

    if (response.status !== 200) {
        return false;
    }
    document.getElementById(id).children[2].parentElement.removeChild(document.getElementById(id).children[2]);
};

// Cookies
const setCookie = (cname, cvalue) => {
    const d = new Date();
    d.setTime(d.getTime() + (60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
const getCookie = (cname) => {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let cookies = decodedCookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return "";
}
const removeCookie = (cname) => {
    const d = new Date();
    d.setTime(d.getTime() - (60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=;" + expires + ";path=/";
}

const showLoader = () => {
    const div = document.createElement("div");
    div.classList.add("loader-panel");
    div.id = "loader-panel";
    const loader = document.createElement("div");
    loader.classList.add("loader");
    div.appendChild(loader);
    document.body.appendChild(div);
}

const hideLoader = () => {
    document.body.removeChild(document.getElementById("loader-panel"));
}
const limpiarPagina = () => {
    if (document.getElementById("selectCategoria") !== null) { document.getElementById("selectCategoria").parentElement.removeChild(document.getElementById("selectCategoria")); }
    if (document.getElementById("tabla") !== null) { document.getElementById("tabla").parentElement.removeChild(document.getElementById("tabla")); }
    if (document.getElementById("divCategorias") !== null) { document.getElementById("divCategorias").parentElement.removeChild(document.getElementById("divCategorias")); }
    if (document.getElementById("divCarrito") !== null) { document.getElementById("divCarrito").parentElement.removeChild(document.getElementById("divCarrito")); }
}
