// src/pages/Return.tsx

import "../styles/return.css";

import {
  ArrowLeft,
  RotateCcw,
  Plus,
  Trash2,
  CheckCircle2,
  ScanLine,
  AlertCircle,
} from "lucide-react";

import { useNavigate } from "react-router-dom";

import {
  useState,
  useRef,
  useEffect,
} from "react";

import axios from "axios";

/* =========================
   TYPES
========================= */

interface AssetItem {
  id: number;
  asset: string;
  barcode: string;
}

interface BagItem {
  id: number;
  code: string;
  store: string;
  status: string;

  details: AssetItem[];

  checkedItems: string[];

  completed: boolean;
}

/* =========================
   COMPONENT
========================= */

export default function Return() {

  const navigate = useNavigate();

  /* =========================
     STATES
  ========================= */

  const [barcode, setBarcode] =
    useState("");

  const [bags, setBags] =
    useState<BagItem[]>([]);

  const [currentBagId, setCurrentBagId] =
    useState<number | null>(null);

  /* =========================
     INPUT REF
  ========================= */

  const inputRef =
    useRef<HTMLInputElement>(null);

  const isScanning =
    useRef(false);

  /* =========================
     MODAL
  ========================= */

  const [showModal, setShowModal] =
    useState(false);

  const [employeeName, setEmployeeName] =
    useState("");
    const [assetNotes, setAssetNotes] =
  useState<Record<string, string>>({});


  /* =========================
     ALERT
  ========================= */

  const [toast, setToast] = useState({
    show: false,
    type: "",
    message: "",
  });

  /* =========================
     AUTO FOCUS
  ========================= */

  useEffect(() => {

    inputRef.current?.focus();

  }, [bags, currentBagId]);

  /* =========================
     ALERT FUNCTION
  ========================= */

const showToast = (
  type: string,
  message: string
) => {

  setToast({
    show: true,
    type,
    message,
  });

  setTimeout(() => {

    setToast({
      show: false,
      type: "",
      message: "",
    });

  }, 2200);
};

  /* =========================
     CURRENT BAG
  ========================= */

  const currentBag =
    bags.find(
      (bag) =>
        bag.id === currentBagId
    );

  /* =========================
     ADD BAG
  ========================= */

  const handleAddBag =
    async () => {

      if (!barcode.trim())
        return;

      const cleanBarcode =
        barcode.trim();

      const alreadyExist =
        bags.find(
          (bag) =>
            bag.code ===
            cleanBarcode
        );

      if (alreadyExist) {

        showToast(
          "warning",
          "Tas sudah discan"
        );

        setBarcode("");

        return;
      }

      try {

        const res =
          await axios.post(
            "http://127.0.0.1:8000/api/return/scan-bag",
            {
              barcode:
                cleanBarcode,
            }
          );

        if (!res.data.success) {

          showToast(
            "error",
            res.data.message
          );
          return;
        }

        const bag =
          res.data.bag;

        if (
          bag.status ===
          "available"
        ) {

          showToast(
            "warning",
            "Tas sudah dikembalikan"
          );

          return;
        }

        const detailRes =
          await axios.get(
            `http://127.0.0.1:8000/api/return/details/${bag.barcode}`
          );

        const newBag: BagItem = {

          id: bag.id,

          code: bag.barcode,

          store:
            bag.name_store,

          status:
            "Returned",

          details:
            detailRes.data
              .details || [],

          checkedItems: [],

          completed: false,
        };

        setBags((prev) => [
          newBag,
          ...prev,
        ]);

        /*
        =========================
        AUTO ACTIVE BAG
        =========================
        */

        setCurrentBagId(
          bag.id
        );

        setBarcode("");

      } catch (err) {

        console.log(err);

        showToast(
          "error",
          "Server error"
        );
      }
    };

  /* =========================
     SCAN ITEM
  ========================= */

  const handleScanItem = async () => {

      if (!currentBag)
        return;

        const cleanBarcode =
  barcode.trim();

console.log(
  "SCAN:",
  cleanBarcode
);

currentBag.details.forEach(
  (item) => {

    console.log(
      "DB:",
      item.barcode
    );

  }
);

const found =
  currentBag.details.find(
    (item) =>
      item.barcode ===
      cleanBarcode
  );
      
        if (!found) {

          showToast(
            "error",
            "Asset tidak ditemukan"
          );
        
          setBarcode("");
        
          return;
        }

        try {

          await axios.post(
            "http://127.0.0.1:8000/api/return/scan-item",
            {
              barcode: found.barcode,
              bag_id: currentBag.id,
            }
          );
        
        } catch (err) {
        
          showToast(
            "error",
            "Gagal update device"
          );
        
          return;
        }

      /*
      =========================
      DOUBLE ITEM
      =========================
      */

      if (
        currentBag.checkedItems.includes(
          found.barcode
        )
      ) {

        showToast(
          "warning",
          "Item sudah discan"
        );

        setBarcode("");

        return;
      }

      /*
      =========================
      UPDATE ITEM
      =========================
      */

      const updatedChecked = [
        ...currentBag.checkedItems,
        found.barcode,
      ];

      const isComplete =
        updatedChecked.length ===
        currentBag.details.length;

      setBags((prev) =>
        prev.map((bag) => {

          if (
            bag.id !==
            currentBag.id
          ) {
            return bag;
          }

          return {

            ...bag,

            checkedItems:
              updatedChecked,

            completed:
              isComplete,
          };
        })
      );

      /*
      =========================
      RESET INPUT
      =========================
      */

      setBarcode("");

      /*
      =========================
      JIKA COMPLETE
      =========================
      */

      if (isComplete) {

        /*
        =========================
        AUTO PINDAH KE TAS BERIKUTNYA
        =========================
        */

        const nextBag =
          bags.find(
            (bag) =>
              bag.id !==
                currentBag.id &&
              !bag.completed
          );

        if (nextBag) {

          setCurrentBagId(
            nextBag.id
          );

        } else {

          setCurrentBagId(
            null
          );
        }
      }
    };

  /* =========================
     DELETE BAG
  ========================= */

  const handleDelete =
    (id: number) => {

      setBags((prev) =>
        prev.filter(
          (bag) =>
            bag.id !== id
        )
      );

      if (
        currentBagId === id
      ) {

        setCurrentBagId(
          null
        );
      }
    };

  /* =========================
     VALIDATE
  ========================= */

  const handleValidate =
    () => {

      if (bags.length === 0) {

       showToast(
          "error",
          "Belum ada tas dipilih"
        );

        return;
      }

      setShowModal(true);
    };

  /* =========================
     SAVE RETURN
  ========================= */


  
  const handleSaveReturn =
    async () => {

      console.log("=== SAVE DIPANGGIL ===");

      console.log("employeeName:", employeeName);

      console.log("bags:", bags);

      console.log("assetNotes:", assetNotes);

      if (
        !employeeName.trim()
    ) {

      showToast(
        "error",
        "Nama pengembali wajib diisi"
      );

        return;
      }

      try {

        for (const bag of bags) {

          console.log({
            bag_id: bag.id,
            employee_name: employeeName,
            notes: assetNotes,
          });

          await axios.post(
            "http://127.0.0.1:8000/api/return/save",
            {
              bag_id: bag.id,

              employee_name:
                employeeName,

              notes: assetNotes,
            }
          );
        }

        setShowModal(false);

        showToast(
          "success",
          "Pengembalian berhasil"
        );

        setTimeout(() => {

          navigate("/");

        }, 1200);

      } catch (err) {

        console.log(err);

        showToast(
          "error",
          "Gagal return"
        );
      }
    };

  /* =========================
     ENTER SCAN
  ========================= */

  const handleKeyDown =
    async (
      e: React.KeyboardEvent<HTMLInputElement>
    ) => {

      if (
        e.key !== "Enter"
      )
        return;

      e.preventDefault();

      if (
        isScanning.current
      )
        return;

      isScanning.current =
        true;

      try {

        if (
          !barcode.trim()
        )
          return;

        /*
        =========================
        JIKA ADA TAS ACTIVE
        MAKA SCAN ITEM
        =========================
        */

        if (currentBag) {

          await handleScanItem();
        
        } else {
        
          await handleAddBag();
        }

      } finally {

        setTimeout(() => {

          isScanning.current =
            false;

          inputRef.current?.focus();

        }, 250);
      }
    };

  return (

    <div className="pickup-page">

      {/* ALERT */}

      {toast.show && (

<div className={`center-alert alert-${toast.type}`}>

  <div className="center-alert-icon">

    {toast.type === "success" && "✓"}

    {toast.type === "warning" && "!"}

    {toast.type === "error" && "✕"}

  </div>

  <h3>
    {toast.message}
  </h3>

  <p>

    {toast.type === "success" &&
      "Data berhasil diproses"}

    {toast.type === "warning" &&
      "Periksa kembali data yang discan"}

    {toast.type === "error" &&
      "Tidak terdaftar dalam sistem"}

  </p>

</div>

)}

      {/* MODAL */}

      {showModal && (

        <div className="modal-overlay">

          <div className="modal-box">

            <h2>
              Validasi Pengembalian
            </h2>

            <p>
              Masukkan nama pengembali tas
            </p>

            <input
              type="text"
              placeholder="Nama pengembali"
              value={employeeName}
              onChange={(e) =>
                setEmployeeName(
                  e.target.value
                )
              }
            />

            <div className="modal-actions">

              <button
                className="cancel-btn"
                onClick={() =>
                  setShowModal(false)
                }
              >
                Batal
              </button>

              <button
                className="save-btn orange-btn"
                onClick={handleSaveReturn}
              >
                Simpan
              </button>
            </div>

          </div>

        </div>
      )}

      {/* HEADER */}

      <div className="pickup-header">

        <div className="pickup-header-left">

          <button
            className="back-button"
            onClick={() =>
              navigate("/")
            }
          >
            <ArrowLeft size={20} />
          </button>

          <h1>
            Pengembalian Tas
          </h1>

        </div>

        <div className="selected-badge orange-badge">

          {bags.length} Tas Dipilih

        </div>

      </div>

      {/* CONTENT */}

      <div className="pickup-content">

        {/* LEFT */}

        <div className="scanner-card">

          <div className="scanner-title">

            <div className="scanner-icon orange">

              <RotateCcw size={22} />

            </div>

            <div>

              <h2>
                Scan Barcode
              </h2>

              <p>
                Arahkan scanner ke barcode tas
              </p>

            </div>

          </div>

          {/* INPUT */}

          <div className="manual-input-wrapper">

            <div className="manual-input orange-border">

              <ScanLine size={20} />

              <input
                ref={inputRef}
                type="text"
                placeholder={
                  currentBag
                    ? `Scan asset untuk ${currentBag.code}`
                    : "Scan barcode tas"
                }
                value={barcode}
                onChange={(e) =>
                  setBarcode(
                    e.target.value
                  )
                }
                onKeyDown={
                  handleKeyDown
                }
                autoFocus
              />

            </div>

            <button
              className="add-btn orange-btn"
              onClick={
                handleAddBag
              }
            >
              <Plus size={20} />
            </button>

          </div>

          {/* TIPS */}

          <div className="tips-box">

            <div className="tips-icon orange-tip">

              <AlertCircle size={15} />

            </div>

            <div>

              <h4>
                Tips: Arahkan scanner ke barcode tas
              </h4>

              <p>
                Input akan otomatis terbaca
              </p>

            </div>

          </div>

        </div>

        {/* RIGHT */}

        <div className="result-card">

          <div className="result-header">

            <h2>
              Tas Dikembalikan
            </h2>

            <div className="count-badge orange-count">

              {bags.length}

            </div>

          </div>

          {/* LIST */}

          <div className="bag-list">

            {bags.length ===
            0 ? (

              <div className="empty-state">

                <RotateCcw size={40} />

                <h3>
                  Belum ada tas discan
                </h3>

              </div>

            ) : (

              bags.map((bag) => {

                const checkedCount =
                  bag.checkedItems
                    .length;

                const totalAssets =
                  bag.details
                    .length;

                return (

                  <div
                    className={`bag-item ${
                      currentBagId ===
                      bag.id
                        ? "active-bag"
                        : ""
                    }`}
                    key={bag.id}
                  >

                    <div className="bag-top">

                      {/* HEADER */}

                      <div className="bag-header-row">

                        <div className="bag-info">

                          <h3>
                            {bag.code}
                          </h3>

                          <span>
                            {bag.store}
                          </span>

                        </div>

                        <div className="bag-actions">

                          <div className="status returned">

                            <CheckCircle2 size={14} />

                            {
                              checkedCount
                            }
                            /
                            {
                              totalAssets
                            }

                          </div>

                          <button
                            className="delete-btn"
                            onClick={() =>
                              handleDelete(
                                bag.id
                              )
                            }
                          >
                            <Trash2 size={16} />
                          </button>

                        </div>

                      </div>

                      {/* DETAIL */}

                      {!bag.completed && (

                        <div className="inside-detail-list">

                          {bag.details.map(
                            (
                              item
                            ) => (

                                <div
                                  className="inside-detail-item"
                                  key={
                                    item.id
                                  }
                                >

                                  <div className="inside-detail-info">

                                    <strong>
                                      {
                                        item.asset
                                      }
                                    </strong>

                                    <p>
                                      {
                                        item.barcode
                                      }
                                    </p>

                                    <div className="asset-note-wrapper">

                                      <label className="asset-note-label">
                                        Masukan catatan ketika terjadi kendala (wajib)
                                      </label>

                                      <input
                                        type="text"
                                        className="asset-note-input"
                                        placeholder="isi ketika terjadi kendala!"
                                        value={
                                          assetNotes[item.barcode] || ""
                                        }
                                        onChange={(e) =>
                                          setAssetNotes((prev) => ({
                                            ...prev,
                                            [item.barcode]:
                                              e.target.value,
                                          }))
                                        }
                                      />

                                    </div>


                                  </div>

                                  {bag.checkedItems.includes(
                                  item.barcode
                                ) ? (

                                  <CheckCircle2
                                    size={18}
                                    color="#53c26b"
                                  />

                                ) : (

                                  <div className="pending-dot" />

                                )}
                                </div>
                              )
                            )}

                        </div>

                      )}

                      {/* COMPLETE */}

                      {bag.completed && (

                        <div className="all-complete">

                          <CheckCircle2 size={18} />

                          Semua asset selesai

                        </div>

                      )}

                    </div>

                  </div>

                );
              })

            )}

          </div>

          {/* VALIDATE */}

          <button
            className="validate-btn orange-btn"
            onClick={
              handleValidate
            }
          >

            Validasi {bags.length} Pengembalian

          </button>

        </div>

      </div>

    </div>
  );
}