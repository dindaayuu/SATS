import "../styles/dashboard.css";
import logo from "../assets/logo-saloka.png";

import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import axios from "axios";

import {
  PackageCheck,
  Undo2,
} from "lucide-react";


interface Bag {
  id: number;
  name: string;
  name_store: string;
  employee_name?: string;
  pickup_time?: string;
  updated_at?: string;
}


export default function Dashboard() {

  const [time, setTime] =
    useState(new Date());

  const [availableBags, setAvailableBags] =
    useState<Bag[]>([]);

  const [usedBags, setUsedBags] =
    useState<Bag[]>([]);

  const [searchAvailable, setSearchAvailable] =
    useState("");

  const [searchUsed, setSearchUsed] =
    useState("");


  useEffect(() => {

    const timer =
      setInterval(() => {
        setTime(new Date());
      }, 1000);


    fetchDashboard();


    return () =>
      clearInterval(timer);

  }, []);



  const fetchDashboard = async () => {

    try {

      const res =
        await axios.get(
          "http://127.0.0.1:8000/api/dashboard"
        );


      setAvailableBags(
        res.data.available_bags || []
      );


      setUsedBags(
        res.data.used_bags || []
      );


    } catch (err) {

      console.log(err);

    }

  };



  const formattedDate =
    time.toLocaleDateString(
      "id-ID",
      {
        day:"numeric",
        month:"long",
        year:"numeric",
      }
    );


  const formattedTime =
    time.toLocaleTimeString(
      "id-ID"
    );



  const filteredAvailableBags =
    availableBags.filter(
      (bag)=>

        bag.name
        .toLowerCase()
        .includes(
          searchAvailable.toLowerCase()
        )

        ||

        bag.name_store
        .toLowerCase()
        .includes(
          searchAvailable.toLowerCase()
        )

    );



  const filteredUsedBags =
    usedBags.filter(
      (bag)=>

        bag.name
        .toLowerCase()
        .includes(
          searchUsed.toLowerCase()
        )

        ||

        bag.name_store
        .toLowerCase()
        .includes(
          searchUsed.toLowerCase()
        )

        ||

        (bag.employee_name || "")
        .toLowerCase()
        .includes(
          searchUsed.toLowerCase()
        )

    );




  return (

    <div className="dashboard">


      {/* HEADER */}

      <div className="header">


        <div className="header-left">

          <img
            src={logo}
            className="logo"
          />


          <div className="title">

            <h1>
              SATS
            </h1>


            <p>
              Smart Asset Tracking Saloka
            </p>

          </div>


        </div>



        <div className="date-box">

          📅 {formattedDate}
          {" "}
          pukul
          {" "}
          {formattedTime}

        </div>


      </div>





      {/* MENU */}

      <div className="menu-grid">


        <Link
          to="/pickup"
          className="menu-card"
        >


          <div className="menu-icon green-icon">

            <PackageCheck
              size={42}
              strokeWidth={2.2}
            />

          </div>



          <div className="menu-content">


            <div>

              <h2>
                Ambil Tas
              </h2>


              <p>
                Proses pengambilan Tas Tenant
              </p>

            </div>

          </div>



          <div className="circle green-circle"/>


        </Link>





        <Link
          to="/return"
          className="menu-card"
        >


          <div className="menu-icon orange-icon">

            <Undo2
              size={42}
              strokeWidth={2.2}
            />

          </div>



          <div className="menu-content">


            <div>

              <h2>
                Kembali Tas
              </h2>


              <p>
                Proses pengembalian Tas Tenant
              </p>

            </div>


          </div>



          <div className="circle orange-circle"/>


        </Link>


      </div>
      {/* TABLE */}

      <div className="table-grid">


        {/* TAS TERSEDIA */}

        <div className="table-card">


          <div className="table-top">


            <div className="table-title">

              <h3>
                Tas Tersedia
              </h3>


              <p>
                Daftar tas yang masih tersedia
              </p>

            </div>



            <input
              className="header-search"
              type="text"
              placeholder="🔍 Cari tas..."
              value={searchAvailable}
              onChange={(e)=>
                setSearchAvailable(
                  e.target.value
                )
              }
            />



            <div className="count green-count">

              {availableBags.length} Tas

            </div>


          </div>





          <div className="table-header">

            <div>
              Nama Tas
            </div>

            <div>
              Store
            </div>

            <div>
              Status
            </div>

          </div>





          <div className="table-body">


            {
              filteredAvailableBags.map(
                (bag)=>(


                <div
                  className="table-row"
                  key={bag.id}
                >


                  <div>
                    {bag.name}
                  </div>


                  <div>
                    {bag.name_store}
                  </div>


                  <div>

                    <span className="status available">

                      Tersedia

                    </span>

                  </div>


                </div>


              ))

            }


          </div>


        </div>







        {/* TAS DIPAKAI */}

        <div className="table-card">


          <div className="table-top">


            <div className="table-title">

              <h3>
                Tas Dipakai
              </h3>


              <p>
                Daftar tas yang sedang dipakai
              </p>

            </div>




            <input
              className="header-search"
              type="text"
              placeholder="🔍 Cari tas..."
              value={searchUsed}
              onChange={(e)=>
                setSearchUsed(
                  e.target.value
                )
              }
            />




            <div className="badge orange-badge">

              {usedBags.length} Tas

            </div>


          </div>






          <div className="used-header">

            <div>
              Nama Tas
            </div>

            <div>
              Store
            </div>

            <div>
              Pengambil
            </div>

            <div>
              Jam Diambil
            </div>

            <div>
              Status
            </div>

          </div>






          <div className="table-scroll">


            {
              filteredUsedBags.length === 0 ? (

                <div className="empty-used">

                  Belum ada tas dipakai

                </div>


              ) : (


                filteredUsedBags.map(
                  (bag)=>(


                  <div
                    className="used-row"
                    key={bag.id}
                  >


                    <div>
                      {bag.name}
                    </div>


                    <div>
                      {bag.name_store}
                    </div>


                    <div>

                      {
                        bag.employee_name || "-"
                      }

                    </div>


                    <div>

                      {
                        bag.pickup_time
                        ?
                        new Date(
                          bag.pickup_time
                        )
                        .toLocaleTimeString(
                          "id-ID",
                          {
                            hour:"2-digit",
                            minute:"2-digit",
                          }
                        )

                        :

                        "-"
                      }

                    </div>




                    <div>

                      <span className="status taken">

                        Dipakai

                      </span>

                    </div>


                  </div>


                ))

              )

            }


          </div>


        </div>


      </div>


    </div>

  );

}