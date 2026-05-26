import { Link, useLocation } from "react-router";
import { useAuth } from "@/hooks/useAuth";
import { useState } from "react";
import { Train, Menu, X, LayoutDashboard, BarChart3, LogOut, User, Ticket } from "lucide-react";

export default function Navbar() {
  const { user, isAuthenticated, logout } = useAuth();
  const location = useLocation();
  const [mobileOpen, setMobileOpen] = useState(false);
  const isAdmin = user?.role === "admin";

  const isActive = (path: string) => location.pathname === path;

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 glass-card border-b border-pink-500/20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-3 group">
            <div className="relative">
              <Train className="w-8 h-8 text-[#ff00ff] group-hover:text-[#00ff88] transition-colors" />
              <div className="absolute inset-0 bg-[#ff00ff] blur-lg opacity-50 group-hover:bg-[#00ff88] transition-colors" />
            </div>
            <span className="kai-lightning text-2xl text-white">KAI</span>
            <span className="hidden sm:block text-xs text-gray-400 uppercase tracking-widest">Kereta Api Indonesia</span>
          </Link>

          {/* Desktop Nav */}
          <div className="hidden md:flex items-center gap-6">
            <Link
              to="/"
              className={`text-sm font-medium transition-colors ${
                isActive("/") ? "text-[#ff00ff]" : "text-gray-300 hover:text-[#00ff88]"
              }`}
            >
              Home
            </Link>
            <Link
              to="/schedule"
              className={`text-sm font-medium transition-colors ${
                isActive("/schedule") ? "text-[#ff00ff]" : "text-gray-300 hover:text-[#00ff88]"
              }`}
            >
              Schedule
            </Link>
            {isAdmin && (
              <>
                <Link
                  to="/admin"
                  className={`text-sm font-medium transition-colors flex items-center gap-1 ${
                    isActive("/admin") ? "text-[#ff00ff]" : "text-gray-300 hover:text-[#00ff88]"
                  }`}
                >
                  <LayoutDashboard className="w-4 h-4" />
                  Dashboard
                </Link>
                <Link
                  to="/admin/analytics"
                  className={`text-sm font-medium transition-colors flex items-center gap-1 ${
                    isActive("/admin/analytics") ? "text-[#ff00ff]" : "text-gray-300 hover:text-[#00ff88]"
                  }`}
                >
                  <BarChart3 className="w-4 h-4" />
                  Analytics
                </Link>
              </>
            )}
            {isAuthenticated ? (
              <div className="flex items-center gap-4">
                <span className="text-sm text-[#00ff88] flex items-center gap-1">
                  <User className="w-4 h-4" />
                  {user?.name || "User"}
                </span>
                <button
                  onClick={logout}
                  className="text-sm text-gray-300 hover:text-[#ff00ff] transition-colors flex items-center gap-1"
                >
                  <LogOut className="w-4 h-4" />
                  Logout
                </button>
              </div>
            ) : (
              <Link
                to="/login"
                className="text-sm font-medium bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white px-4 py-2 rounded-lg hover:shadow-[0_0_15px_rgba(255,0,255,0.5)] transition-all"
              >
                Login
              </Link>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={() => setMobileOpen(!mobileOpen)}
            className="md:hidden text-white p-2"
          >
            {mobileOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileOpen && (
        <div className="md:hidden glass-card border-t border-pink-500/20">
          <div className="px-4 py-4 space-y-3">
            <Link to="/" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff]">Home</Link>
            <Link to="/schedule" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff]">Schedule</Link>
            {isAdmin && (
              <>
                <Link to="/admin" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff] flex items-center gap-2">
                  <LayoutDashboard className="w-4 h-4" /> Dashboard
                </Link>
                <Link to="/admin/analytics" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff] flex items-center gap-2">
                  <BarChart3 className="w-4 h-4" /> Analytics
                </Link>
                <Link to="/admin/trains" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff] flex items-center gap-2">
                  <Train className="w-4 h-4" /> Manage Trains
                </Link>
                <Link to="/admin/bookings" onClick={() => setMobileOpen(false)} className="block text-gray-300 hover:text-[#ff00ff] flex items-center gap-2">
                  <Ticket className="w-4 h-4" /> Bookings
                </Link>
              </>
            )}
            {isAuthenticated ? (
              <button onClick={() => { logout(); setMobileOpen(false); }} className="block text-gray-300 hover:text-[#ff00ff]">
                Logout
              </button>
            ) : (
              <Link to="/login" onClick={() => setMobileOpen(false)} className="block text-[#00ff88]">Login</Link>
            )}
          </div>
        </div>
      )}
    </nav>
  );
}
