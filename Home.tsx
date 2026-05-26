import { Link } from "react-router";
import { useEffect, useRef, useState } from "react";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import {
  Train,
  Zap,
  Calendar,
  MapPin,
  Shield,
  ArrowRight,
  Star,
  Sparkles,
  ChevronRight,
} from "lucide-react";

export default function Home() {
  const { data: trainsData } = trpc.train.list.useQuery();
  const { data: suggestions } = trpc.analytics.suggestions.useQuery();
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [searchFrom, setSearchFrom] = useState("");
  const [searchTo, setSearchTo] = useState("");
  const [searchDate, setSearchDate] = useState("");

  // Lightning animation on canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const w = window.innerWidth;
    const h = window.innerHeight;
    canvas.width = w;
    canvas.height = h;

    const bolts: Array<{
      segments: Array<{ x: number; y: number }>;
      life: number;
      color: string;
    }> = [];

    function createBolt() {
      const startX = Math.random() * w;
      const segments = [{ x: startX, y: 0 }];
      let x = startX;
      for (let y = 0; y < h; y += 20 + Math.random() * 30) {
        x += (Math.random() - 0.5) * 80;
        segments.push({ x, y });
      }
      bolts.push({
        segments,
        life: 1,
        color: Math.random() > 0.5 ? "#ff00ff" : "#00ff88",
      });
    }

    let frame = 0;
    let animId: number;

    function animate() {
      ctx!.clearRect(0, 0, w, h);
      frame++;

      if (frame % 60 === 0) createBolt();
      if (frame % 90 === 0) createBolt();

      bolts.forEach((bolt, i) => {
        bolt.life -= 0.02;
        if (bolt.life <= 0) {
          bolts.splice(i, 1);
          return;
        }

        ctx!.beginPath();
        ctx!.strokeStyle = bolt.color;
        ctx!.lineWidth = 2;
        ctx!.shadowBlur = 20;
        ctx!.shadowColor = bolt.color;
        ctx!.globalAlpha = bolt.life;

        ctx!.moveTo(bolt.segments[0].x, bolt.segments[0].y);
        for (let j = 1; j < bolt.segments.length; j++) {
          ctx!.lineTo(bolt.segments[j].x, bolt.segments[j].y);
        }
        ctx!.stroke();
        ctx!.globalAlpha = 1;
        ctx!.shadowBlur = 0;
      });

      animId = requestAnimationFrame(animate);
    }

    animate();

    const handleResize = () => {
      const nw = window.innerWidth;
      const nh = window.innerHeight;
      canvas.width = nw;
      canvas.height = nh;
    };
    window.addEventListener("resize", handleResize);
    return () => {
      window.removeEventListener("resize", handleResize);
      cancelAnimationFrame(animId);
    };
  }, []);

  const featuredTrains = trainsData?.slice(0, 4) || [];

  return (
    <div className="min-h-screen bg-black">
      <Navbar />

      {/* Hero Section with Lightning */}
      <section className="relative h-screen flex items-center justify-center overflow-hidden">
        <canvas
          ref={canvasRef}
          className="absolute inset-0 pointer-events-none"
          style={{ zIndex: 1 }}
        />

        {/* Background Image */}
        <div
          className="absolute inset-0 bg-cover bg-center opacity-30"
          style={{ backgroundImage: "url(/hero-train.jpg)" }}
        />
        <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black" />

        {/* Hero Content */}
        <div className="relative z-10 text-center px-4 max-w-5xl mx-auto">
          <div className="mb-6 inline-block">
            <div className="relative">
              <h1 className="kai-lightning-intense text-7xl sm:text-8xl md:text-9xl tracking-wider">
                KAI
              </h1>
              <div className="absolute -inset-4 bg-gradient-to-r from-[#ff00ff]/20 to-[#00ff88]/20 blur-2xl rounded-full" />
            </div>
          </div>

          <p className="text-xl sm:text-2xl text-gray-300 mb-2 tracking-widest uppercase">
            Kereta Api Indonesia
          </p>
          <p className="text-[#00ff88] text-lg mb-8 flex items-center justify-center gap-2">
            <Zap className="w-5 h-5" />
            Futuristic Railway Ticketing System
            <Zap className="w-5 h-5" />
          </p>

          {/* Search Box */}
          <div className="glass-card rounded-2xl p-6 max-w-3xl mx-auto mb-8 neon-border">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="relative">
                <MapPin className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
                <input
                  type="text"
                  placeholder="From Station"
                  value={searchFrom}
                  onChange={(e) => setSearchFrom(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] focus:shadow-[0_0_10px_rgba(255,0,255,0.3)] transition-all"
                />
              </div>
              <div className="relative">
                <MapPin className="absolute left-3 top-3 w-5 h-5 text-[#00ff88]" />
                <input
                  type="text"
                  placeholder="To Station"
                  value={searchTo}
                  onChange={(e) => setSearchTo(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 bg-black/50 border border-green-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#00ff88] focus:shadow-[0_0_10px_rgba(0,255,136,0.3)] transition-all"
                />
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
                <input
                  type="date"
                  value={searchDate}
                  onChange={(e) => setSearchDate(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] focus:shadow-[0_0_10px_rgba(255,0,255,0.3)] transition-all"
                />
              </div>
            </div>
            <Link
              to={`/schedule?from=${searchFrom}&to=${searchTo}&date=${searchDate}`}
              className="mt-4 w-full bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white py-3 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all flex items-center justify-center gap-2"
            >
              <Train className="w-5 h-5" />
              Search Trains
              <ArrowRight className="w-5 h-5" />
            </Link>
          </div>

          {/* Stats */}
          <div className="flex flex-wrap justify-center gap-8 text-sm">
            <div className="flex items-center gap-2 text-gray-400">
              <Train className="w-5 h-5 text-[#ff00ff]" />
              <span>{trainsData?.length || 12}+ Trains</span>
            </div>
            <div className="flex items-center gap-2 text-gray-400">
              <MapPin className="w-5 h-5 text-[#00ff88]" />
              <span>12 Major Cities</span>
            </div>
            <div className="flex items-center gap-2 text-gray-400">
              <Shield className="w-5 h-5 text-[#ff00ff]" />
              <span>Secure Booking</span>
            </div>
          </div>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 z-10">
          <div className="w-6 h-10 border-2 border-[#ff00ff] rounded-full flex justify-center">
            <div className="w-1.5 h-3 bg-[#00ff88] rounded-full mt-2 animate-bounce" />
          </div>
        </div>
      </section>

      {/* Featured Trains */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-3xl sm:text-4xl font-bold gradient-text mb-4">
              Featured Trains
            </h2>
            <p className="text-gray-400 max-w-2xl mx-auto">
              Experience the future of rail travel with our premium fleet of high-speed trains
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {featuredTrains.map((train) => (
              <div
                key={train.id}
                className="glass-card rounded-2xl overflow-hidden hover-glow group"
              >
                <div className="relative h-48 overflow-hidden">
                  <img
                    src={train.imageUrl || "/hero-train.jpg"}
                    alt={train.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black to-transparent" />
                  <div className="absolute top-3 right-3">
                    <span className={train.type === "VIP" ? "badge-vip" : "badge-economy"}>
                      {train.type}
                    </span>
                  </div>
                </div>
                <div className="p-4">
                  <h3 className="text-lg font-bold text-white mb-1">{train.name}</h3>
                  <p className="text-gray-400 text-sm mb-3 line-clamp-2">{train.description}</p>
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-1 text-sm text-gray-500">
                      <Star className="w-4 h-4 text-[#ff00ff]" />
                      <span>4.8</span>
                    </div>
                    <Link
                      to="/schedule"
                      className="text-[#00ff88] text-sm font-medium flex items-center gap-1 hover:text-[#ff00ff] transition-colors"
                    >
                      Book Now <ChevronRight className="w-4 h-4" />
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* VIP vs Economy Section */}
      <section className="py-20 px-4 bg-gradient-to-b from-black to-gray-900/50">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-3xl sm:text-4xl font-bold gradient-text mb-4">
              Choose Your Class
            </h2>
            <p className="text-gray-400">
              From luxurious VIP cabins to comfortable Economy seats
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* VIP Card */}
            <div className="glass-card rounded-2xl p-8 neon-border relative overflow-hidden group">
              <div className="absolute top-0 right-0 w-32 h-32 bg-[#ff00ff]/10 rounded-full blur-3xl group-hover:bg-[#ff00ff]/20 transition-all" />
              <div className="relative z-10">
                <div className="flex items-center gap-3 mb-6">
                  <Star className="w-8 h-8 text-[#ff00ff]" />
                  <h3 className="text-2xl font-bold text-white">VIP Class</h3>
                  <span className="badge-vip">Premium</span>
                </div>
                <ul className="space-y-3 mb-8">
                  {[
                    "Luxurious reclining seats with extra legroom",
                    "Complimentary meals and beverages",
                    "Priority boarding and check-in",
                    "Personal entertainment system",
                    "Power outlets and WiFi",
                    "Exclusive lounge access",
                  ].map((feature, i) => (
                    <li key={i} className="flex items-center gap-2 text-gray-300">
                      <Zap className="w-4 h-4 text-[#ff00ff] flex-shrink-0" />
                      {feature}
                    </li>
                  ))}
                </ul>
                <p className="text-3xl font-bold text-[#ff00ff] mb-4">
                  From Rp 350.000
                </p>
                <Link
                  to="/schedule"
                  className="block w-full text-center bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white py-3 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all"
                >
                  Book VIP Class
                </Link>
              </div>
            </div>

            {/* Economy Card */}
            <div className="glass-card-green rounded-2xl p-8 relative overflow-hidden group">
              <div className="absolute top-0 right-0 w-32 h-32 bg-[#00ff88]/10 rounded-full blur-3xl group-hover:bg-[#00ff88]/20 transition-all" />
              <div className="relative z-10">
                <div className="flex items-center gap-3 mb-6">
                  <Train className="w-8 h-8 text-[#00ff88]" />
                  <h3 className="text-2xl font-bold text-white">Economy Class</h3>
                  <span className="badge-economy">Standard</span>
                </div>
                <ul className="space-y-3 mb-8">
                  {[
                    "Comfortable seating with ample space",
                    "Affordable pricing for all travelers",
                    "Clean and well-maintained carriages",
                    "Air conditioning throughout",
                    "Luggage storage available",
                    "Onboard snack service",
                  ].map((feature, i) => (
                    <li key={i} className="flex items-center gap-2 text-gray-300">
                      <Zap className="w-4 h-4 text-[#00ff88] flex-shrink-0" />
                      {feature}
                    </li>
                  ))}
                </ul>
                <p className="text-3xl font-bold text-[#00ff88] mb-4">
                  From Rp 120.000
                </p>
                <Link
                  to="/schedule"
                  className="block w-full text-center bg-gradient-to-r from-[#00ff88] to-[#00cc66] text-black py-3 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(0,255,136,0.5)] transition-all"
                >
                  Book Economy Class
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Route Map Section */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-3xl sm:text-4xl font-bold gradient-text mb-4">
              Network Map
            </h2>
            <p className="text-gray-400">
              Our extensive network connects major cities across Indonesia
            </p>
          </div>

          <div className="glass-card rounded-2xl overflow-hidden neon-border">
            <img
              src="/route-map.jpg"
              alt="Indonesia Railway Route Map"
              className="w-full h-auto"
            />
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mt-8">
            {[
              { city: "Jakarta", code: "GMR", color: "#ff00ff" },
              { city: "Bandung", code: "BD", color: "#00ff88" },
              { city: "Yogyakarta", code: "YK", color: "#ff00ff" },
              { city: "Surabaya", code: "SGU", color: "#00ff88" },
              { city: "Semarang", code: "SMT", color: "#ff00ff" },
              { city: "Malang", code: "ML", color: "#00ff88" },
            ].map((station) => (
              <div
                key={station.code}
                className="glass-card rounded-xl p-4 text-center hover-glow"
              >
                <MapPin className="w-6 h-6 mx-auto mb-2" style={{ color: station.color }} />
                <p className="text-white font-semibold text-sm">{station.city}</p>
                <p className="text-gray-500 text-xs">{station.code}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* AI Suggestions Section */}
      {suggestions && suggestions.length > 0 && (
        <section className="py-20 px-4 bg-gradient-to-b from-gray-900/50 to-black">
          <div className="max-w-7xl mx-auto">
            <div className="text-center mb-12">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Sparkles className="w-8 h-8 text-[#ff00ff]" />
                <h2 className="text-3xl sm:text-4xl font-bold gradient-text">
                  AI Travel Insights
                </h2>
              </div>
              <p className="text-gray-400">
                Smart recommendations powered by booking analytics
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {suggestions.map((suggestion) => (
                <div
                  key={suggestion.id}
                  className="glass-card rounded-2xl p-6 hover-glow"
                >
                  <div className="flex items-center gap-2 mb-3">
                    <Sparkles className="w-5 h-5 text-[#00ff88]" />
                    <span className="text-xs uppercase tracking-wider text-[#ff00ff]">
                      {suggestion.type}
                    </span>
                  </div>
                  <h4 className="text-lg font-bold text-white mb-3">
                    {suggestion.title}
                  </h4>
                  <p className="text-gray-400 text-sm mb-4">{suggestion.content}</p>
                  <div className="flex items-center gap-2">
                    <div className="flex-1 h-2 bg-gray-800 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-[#ff00ff] to-[#00ff88] rounded-full"
                        style={{ width: `${suggestion.confidence}%` }}
                      />
                    </div>
                    <span className="text-xs text-[#00ff88]">{suggestion.confidence}%</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Footer */}
      <footer className="border-t border-pink-500/20 py-12 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex items-center gap-3">
              <Train className="w-8 h-8 text-[#ff00ff]" />
              <span className="kai-lightning text-2xl text-white">KAI</span>
            </div>
            <div className="flex items-center gap-6 text-sm text-gray-400">
              <Link to="/" className="hover:text-[#ff00ff] transition-colors">Home</Link>
              <Link to="/schedule" className="hover:text-[#ff00ff] transition-colors">Schedule</Link>
              <Link to="/admin" className="hover:text-[#ff00ff] transition-colors">Admin</Link>
            </div>
            <p className="text-gray-600 text-sm">
              &copy; 2025 KAI - Kereta Api Indonesia. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
}
