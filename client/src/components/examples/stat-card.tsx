import { StatCard } from '../stat-card';
import { DollarSign } from 'lucide-react';

export default function StatCardExample() {
  return (
    <div className="p-6">
      <StatCard
        title="Total Revenue"
        value="$12,345"
        icon={DollarSign}
        trend={{ value: 12.5, isPositive: true }}
        subtitle="from last month"
      />
    </div>
  );
}
