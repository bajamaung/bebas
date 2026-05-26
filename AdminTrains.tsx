import { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import { useAuth } from "@/hooks/useAuth";
import {
  Train,
  Search,
  ArrowLeft,
  MapPin,
  Edit,
  Eye,
} from "lucide-react";

export default function AdminTrains() {
  const { user, isLoading: authLoading } = useAuth();
  const navigate = useNavigate();
  const isAdmin = user?.role === "admin";
  const [search, setSearch] = useState("");
  const [typeFilter, setTypeFilter] = useState<"ALL" | "VIP" | "Economy">("ALL");

  useEffect(() => {
    if (!authLoading && !isAdmin) navigate("/");
  }, [authLoading, isAdmin, navigate]);

  const { data: trainsData, isLoading } = trpc.train.list.useQuery(undefined, { enabled: isAdmin });
  const { data: stationsData } = trpc.train.stations.useQuery(undefined, { enabled: isAdmin });

  const filteredTrains = trainsData?.filter((train) => {
    const matchesSearch = train.name.toLowerCase().includes(search.toLowerCase()) ||
      train.code.toLowerCase().includes(search.toLowerCase());
    const matchesType = typeFilter === "ALL" || train.type === typeFilter;
    return matchesSearch && matchesType;
  }) || [];

  if (authLoading) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div className="flex items-center gap-4">
            <Link
              to="/admin"
              className="w-10 h-10 rounded-xl border border-pink-500/30 flex items-center justify-center text-[#ff00ff] hover:border-[#ff00ff] hover:shadow-[0_0_10px_rgba(255,0,255,0.3)] transition-all"
            >
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <div>
              <h1 className="text-3xl font-bold gradient-text flex items-center gap-3">
                <Train className="w-8 h-8 text-[#ff00ff]" />
                Train Management
              </h1>
              <p className="text-gray-400 mt-1">Manage trains, routes, and schedules</p>
            </div>
          </div>
        </div>

        {/* Search & Filters */}
        <div className="glass-card rounded-2xl p-4 neon-border mb-6">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search trains..."
                className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
              />
            </div>
            <div className="flex gap-2">
              {(["ALL", "VIP", "Economy"] as const).map((type) => (
                <button
                  key={type}
                  onClick={() => setTypeFilter(type)}
                  className={`px-4 py-2 rounded-xl text-sm font-medium transition-all ${
                    typeFilter === type
                      ? type === "VIP"
                        ? "bg-[#ff00ff] text-white shadow-[0_0_10px_rgba(255,0,255,0.5)]"
                        : type === "Economy"
                        ? "bg-[#00ff88] text-black shadow-[0_0_10px_rgba(0,255,136,0.5)]"
                        : "bg-gray-700 text-white"
                      : "bg-black/50 text-gray-400 border border-gray-700 hover:border-gray-500"
                  }`}
                >
                  {type}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Trains Grid */}
        {isLoading ? (
          <div className="text-center py-20">
            <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin mx-auto" />
          </div>
        ) : filteredTrains.length === 0 ? (
          <div className="text-center py-20">
            <Train className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <h3 className="text-xl font-bold text-white">No trains found</h3>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            {filteredTrains.map((train) => (
              <div key={train.id} className="glass-card rounded-2xl overflow-hidden neon-border hover-glow">
                {/* Train Image */}
                <div className="relative h-48 overflow-hidden">
                  <img
                    src={train.imageUrl || "/hero-train.jpg"}
                    alt={train.name}
                    className="w-full h-full object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent" />
                  <div className="absolute top-3 left-3">
                    <span className={train.type === "VIP" ? "badge-vip" : "badge-economy"}>
                      {train.type}
                    </span>
                  </div>
                  <div className="absolute top-3 right-3">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                      train.status === "active" ? "status-active" :
                      train.status === "maintenance" ? "status-pending" :
                      "bg-red-500/20 text-red-400 border border-red-500/30"
                    }`}>
                      {train.status}
                    </span>
                  </div>
                  <div className="absolute bottom-3 left-3 right-3">
                    <h3 className="text-xl font-bold text-white">{train.name}</h3>
                    <p className="text-sm text-gray-400">{train.code}</p>
                  </div>
                </div>

                {/* Train Details */}
                <div className="p-4">
                  <p className="text-sm text-gray-400 mb-4 line-clamp-2">{train.description}</p>

                  <div className="grid grid-cols-3 gap-3 mb-4">
                    <div className="text-center p-2 rounded-lg bg-black/40">
                      <p className="text-lg font-bold text-white">{train.totalSeats}</p>
                      <p className="text-xs text-gray-500">Total Seats</p>
                    </div>
                    <div className="text-center p-2 rounded-lg bg-pink-500/10">
                      <p className="text-lg font-bold text-[#ff00ff]">{train.vipSeats}</p>
                      <p className="text-xs text-gray-500">VIP</p>
                    </div>
                    <div className="text-center p-2 rounded-lg bg-green-500/10">
                      <p className="text-lg font-bold text-[#00ff88]">{train.economySeats}</p>
                      <p className="text-xs text-gray-500">Economy</p>
                    </div>
                  </div>

                  <div className="flex gap-2">
                    <button className="flex-1 flex items-center justify-center gap-2 py-2 border border-pink-500/30 rounded-lg text-sm text-[#ff00ff] hover:border-[#ff00ff] hover:bg-pink-500/10 transition-all">
                      <Edit className="w-4 h-4" />
                      Edit
                    </button>
                    <button className="flex-1 flex items-center justify-center gap-2 py-2 border border-green-500/30 rounded-lg text-sm text-[#00ff88] hover:border-[#00ff88] hover:bg-green-500/10 transition-all">
                      <Eye className="w-4 h-4" />
                      View
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Stations Reference */}
        <div className="glass-card rounded-2xl p-6 neon-border mt-8">
          <h2 className="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <MapPin className="w-5 h-5 text-[#ff00ff]" />
            Stations Reference
          </h2>
          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            {stationsData?.map((station) => (
              <div key={station.id} className="p-3 rounded-xl bg-black/40 border border-pink-500/10">
                <p className="text-sm font-semibold text-white">{station.name}</p>
                <p className="text-xs text-gray-500">{station.city} ({station.code})</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
