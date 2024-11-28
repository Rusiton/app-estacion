let stations = await fetch("https://mattprofe.com.ar/proyectos/app-estacion/datos.php?mode=list-stations");
stations = await stations.json();

stations.forEach( ({ chipid, apodo, ubicacion, visitas }) => {

    const template = station_template.content.cloneNode(true);

    const station = template.querySelector(".station");

    station.id = chipid;

    station.querySelector(".station_alias").textContent = apodo;
    station.querySelector(".station_alias").id = chipid;

    station.querySelector(".station_location").textContent = ubicacion;
    station.querySelector(".station_location").id = chipid;

    station.querySelector(".station_visits").textContent = visitas;
    station.querySelector(".station_visits").id = chipid;

    stations_table.appendChild(station);

});

stations_table.addEventListener("click", event => {

    if(event.target !== stations_table && event.target.className !== "thead"){
        window.location = "detalle?chipid=" + event.target.id;
    }

});