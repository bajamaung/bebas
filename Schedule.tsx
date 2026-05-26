import { useState } from "react";
import { Link, useSearchParams } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import {
  Train,
  MapPin,
  Clock,
  Calendar,
  Filter,
  ChevronRight,
  Star,
  Users,
} from "lucide-react";

export default function Schedule() {
  const [searchParams] = useSearchParams();
  const [fromStation, setFromStation] = useState(searchParams.get("from") || "");
  const [toStation, setToStation] = useState(searchParams.get("to") || "");
  const [date, setDate] = useState(searchParams.get("date") || "");
  const [selectedType, setSelectedType] = useState<"VIP" | "Economy" | undefined>(undefined);
  const [showFilters, setShowFilters] = useState(false);

  const { data: stationsData } = trpc.train.stations.useQuery();
  const { data: schedulesData, isLoading } = trpc.train.schedules.useQuery({
    date: date || undefined,
    trainType: selectedType,
  });

  // Filter schedules based on search
  const filteredSchedules = schedulesData?.filter((s) => {
    if (fromStation && !s.route?.originStation?.city.toLowerCase().includes(fromStation.toLowerCase())) {
      return false;
    }
    if (toStation && !s.route?.destinationStation?.city.toLowerCase().includes(toStation.toLowerCase())) {
      return false;
    }
    return true;
  }) || [];

  const formatDuration = (minutes: number) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${h}h ${m}m`;
  };

  const formatPrice = (price: string) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(parseFloat(price));
  };

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      {/* Header */}
      <div className="relative py-12 px-4 overflow-hidden">
        <div
          className="absolute inset-0 bg-cover bg-center opacity-20"
          style={{ backgroundImage: "url(/hero-train.jpg)" }}
        />
        <div className="absolute inset-0 bg-gradient-to-b from-black/60 to-black" />
        <div className="relative z-10 max-w-7xl mx-auto text-center">
          <h1 className="text-4xl sm:text-5xl font-bold gradient-text mb-4">
            Train Schedule
          </h1>
          <p className="text-gray-400 max-w-2xl mx-auto">
            Find and book your perfect train journey across Indonesia
          </p>
        </div>
      </div>

      {/* Search & Filter Section */}
      <div className="max-w-7xl mx-auto px-4 mb-8">
        <div className="glass-card rounded-2xl p-6 neon-border">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="relative">
              <MapPin className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
              <select
                value={fromStation}
                onChange={(e) => setFromStation(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white focus:outline-none focus:border-[#ff00ff] transition-all appearance-none"
              >
                <option value="">All Origin Stations</option>
                {stationsData?.map((station) => (
                  <option key={station.id} value={station.city}>
                    {station.name} ({station.city})
                  </option>
                ))}
              </select>
            </div>
            <div className="relative">
              <MapPin className="absolute left-3 top-3 w-5 h-5 text-[#00ff88]" />
              <select
                value={toStation}
                onChange={(e) => setToStation(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-black/50 border border-green-500/30 rounded-xl text-white focus:outline-none focus:border-[#00ff88] transition-all appearance-none"
              >
                <option value="">All Destination Stations</option>
                {stationsData?.map((station) => (
                  <option key={station.id} value={station.city}>
                    {station.name} ({station.city})
                  </option>
                ))}
              </select>
            </div>
            <div className="relative">
              <Calendar className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
              <input
                type="date"
                value={date}
                onChange={(e) => setDate(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white focus:outline-none focus:border-[#ff00ff] transition-all"
              />
            </div>
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center justify-center gap-2 py-3 border border-pink-500/30 rounded-xl text-white hover:border-[#ff00ff] hover:shadow-[0_0_10px_rgba(255,0,255,0.3)] transition-all"
            >
              <Filter className="w-5 h-5 text-[#ff00ff]" />
              Filters
            </button>
          </div>

          {/* Filters */}
          {showFilters && (
            <div className="mt-4 pt-4 border-t border-pink-500/20 flex flex-wrap gap-4">
              <button
                onClick={() => setSelectedType(undefined)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
                  !selectedType
                    ? "bg-[#ff00ff] text-white shadow-[0_0_10px_rgba(255,0,255,0.5)]"
                    : "bg-black/50 text-gray-400 border border-pink-500/30 hover:border-[#ff00ff]"
                }`}
              >
                All Types
              </button>
              <button
                onClick={() => setSelectedType("VIP")}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
                  selectedType === "VIP"
                    ? "bg-[#ff00ff] text-white shadow-[0_0_10px_rgba(255,0,255,0.5)]"
                    : "bg-black/50 text-gray-400 border border-pink-500/30 hover:border-[#ff00ff]"
                }`}
              >
                VIP Only
              </button>
              <button
                onClick={() => setSelectedType("Economy")}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
                  selectedType === "Economy"
                    ? "bg-[#00ff88] text-black shadow-[0_0_10px_rgba(0,255,136,0.5)]"
                    : "bg-black/50 text-gray-400 border border-green-500/30 hover:border-[#00ff88]"
                }`}
              >
                Economy Only
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Results */}
      <div className="max-w-7xl mx-auto px-4 pb-20">
        {isLoading ? (
          <div className="text-center py-20">
            <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin mx-auto mb-4" />
            <p className="text-gray-400">Loading schedules...</p>
          </div>
        ) : filteredSchedules.length === 0 ? (
          <div className="text-center py-20">
            <Train className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <h3 className="text-xl font-bold text-white mb-2">No trains found</h3>
            <p className="text-gray-400">Try adjusting your search criteria</p>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="flex items-center justify-between mb-6">
              <p className="text-gray-400">
                Found <span className="text-[#ff00ff] font-bold">{filteredSchedules.length}</span> trains
              </p>
              <p className="text-gray-500 text-sm">
                {date ? new Date(date).toLocaleDateString("id-ID", { weekday: "long", year: "numeric", month: "long", day: "numeric" }) : "All dates"}
              </p>
            </div>

            {filteredSchedules.map((schedule) => (
              <div
                key={schedule.id}
                className="glass-card rounded-2xl p-6 hover-glow transition-all"
              >
                <div className="flex flex-col lg:flex-row gap-6">
                  {/* Train Image */}
                  <div className="w-full lg:w-48 h-32 rounded-xl overflow-hidden flex-shrink-0">
                    <img
                      src={schedule.train?.imageUrl || "/hero-train.jpg"}
                      alt={schedule.train?.name}
                      className="w-full h-full object-cover"
                    />
                  </div>

                  {/* Train Info */}
                  <div className="flex-1">
                    <div className="flex flex-wrap items-center gap-3 mb-3">
                      <h3 className="text-xl font-bold text-white">
                        {schedule.train?.name}
                      </h3>
                      <span className={schedule.train?.type === "VIP" ? "badge-vip" : "badge-economy"}>
                        {schedule.train?.type}
                      </span>
                      <div className="flex items-center gap-1 text-sm text-gray-500">
                        <Star className="w-4 h-4 text-[#ff00ff]" />
                        <span>4.8</span>
                      </div>
                    </div>

                    {/* Route */}
                    <div className="flex items-center gap-4 mb-4">
                      <div className="text-center">
                        <p className="text-2xl font-bold text-white">
                          {schedule.departureTime?.slice(0, 5)}
                        </p>
                        <p className="text-sm text-[#ff00ff]">
                          {schedule.route?.originStation?.city}
                        </p>
                      </div>

                      <div className="flex-1 flex items-center gap-2">
                        <div className="h-0.5 flex-1 bg-gradient-to-r from-[#ff00ff] to-[#00ff88]" />
                        <div className="text-center">
                          <Clock className="w-4 h-4 text-gray-500 mx-auto" />
                          <p className="text-xs text-gray-500">
                            {formatDuration(schedule.route?.duration || 0)}
                          </p>
                        </div>
                        <div className="h-0.5 flex-1 bg-gradient-to-r from-[#00ff88] to-[#ff00ff]" />
                      </div>

                      <div className="text-center">
                        <p className="text-2xl font-bold text-white">
                          {schedule.arrivalTime?.slice(0, 5)}
                        </p>
                        <p className="text-sm text-[#00ff88]">
                          {schedule.route?.destinationStation?.city}
                        </p>
                      </div>
                    </div>

                    {/* Availability */}
                    <div className="flex flex-wrap gap-4 text-sm">
                      <div className="flex items-center gap-1 text-gray-400">
                        <Calendar className="w-4 h-4" />
                        {schedule.departureDate ? new Date(schedule.departureDate).toLocaleDateString("id-ID") : "N/A"}
                      </div>
                      <div className="flex items-center gap-1 text-[#ff00ff]">
                        <Users className="w-4 h-4" />
                        {schedule.availableVipSeats} VIP seats
                      </div>
                      <div className="flex items-center gap-1 text-[#00ff88]">
                        <Users className="w-4 h-4" />
                        {schedule.availableEconomySeats} Economy seats
                      </div>
                    </div>
                  </div>

                  {/* Pricing & Book */}
                  <div className="flex flex-row lg:flex-col items-center lg:items-end gap-4 lg:border-l lg:border-pink-500/20 lg:pl-6">
                    <div className="text-right">
                      <p className="text-xs text-gray-500 mb-1">Starting from</p>
                      <p className="text-2xl font-bold text-[#00ff88]">
                        {formatPrice(schedule.route?.economyPrice || "0")}
                      </p>
                      <p className="text-sm text-[#ff00ff]">
                        VIP: {formatPrice(schedule.route?.vipPrice || "0")}
                      </p>
                    </div>
                    <Link
                      to={`/booking/${schedule.id}`}
                      className="bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all flex items-center gap-2 whitespace-nowrap"
                    >
                      Book Now
                      <ChevronRight className="w-5 h-5" />
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
