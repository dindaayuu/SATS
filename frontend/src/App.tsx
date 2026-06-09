import { BrowserRouter, Routes, Route } from "react-router-dom";

import Dashboard from "./pages/Dashboard";
import Pickup from "./pages/Pickup";
import ReturnPage from "./pages/Return";

function App() {
  return (
    <BrowserRouter>

      <Routes>

        <Route path="/" element={<Dashboard />} />

        <Route path="/pickup" element={<Pickup />} />

        <Route path="/return" element={<ReturnPage />} />

      </Routes>

    </BrowserRouter>
  );
}

export default App;