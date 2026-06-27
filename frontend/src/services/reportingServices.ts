import axios from "axios";

const api = axios.create({
  baseURL: "http://127.0.0.1:8000/api",
});

export const getSummary = async () => {
  const response =
    await api.get("/reporting/summary");

  return response.data;
};

export const getActivityChart =
  async () => {
    const response =
      await api.get(
        "/reporting/activity-chart"
      );

    return response.data;
  };

export const getTopStores =
  async () => {
    const response =
      await api.get(
        "/reporting/top-stores"
      );

    return response.data;
  };

export const getProblematicDevices =
  async () => {
    const response =
      await api.get(
        "/reporting/problematic-devices"
      );

    return response.data;
  };

export const getUnreturnedDevices =
  async () => {
    const response =
      await api.get(
        "/reporting/unreturned-devices"
      );

    return response.data;
  };