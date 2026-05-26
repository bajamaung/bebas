import { Routes, Route } from 'react-router'
import Home from './pages/Home'
import Schedule from './pages/Schedule'
import Booking from './pages/Booking'
import Ticket from './pages/Ticket'
import AdminDashboard from './pages/AdminDashboard'
import AdminAnalytics from './pages/AdminAnalytics'
import AdminTrains from './pages/AdminTrains'
import AdminBookings from './pages/AdminBookings'
import Login from './pages/Login'
import NotFound from './pages/NotFound'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/schedule" element={<Schedule />} />
      <Route path="/booking/:scheduleId" element={<Booking />} />
      <Route path="/ticket/:bookingId" element={<Ticket />} />
      <Route path="/admin" element={<AdminDashboard />} />
      <Route path="/admin/analytics" element={<AdminAnalytics />} />
      <Route path="/admin/trains" element={<AdminTrains />} />
      <Route path="/admin/bookings" element={<AdminBookings />} />
      <Route path="/login" element={<Login />} />
      <Route path="*" element={<NotFound />} />
    </Routes>
  )
}
