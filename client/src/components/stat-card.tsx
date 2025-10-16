import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { LucideIcon } from "lucide-react";

interface StatCardProps {
  title: string;
  value: string | number;
  icon: LucideIcon;
  trend?: {
    value: number;
    isPositive: boolean;
  };
  subtitle?: string;
}

export function StatCard({ title, value, icon: Icon, trend, subtitle }: StatCardProps) {
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between gap-2 space-y-0 pb-2">
        <h3 className="text-sm font-medium text-muted-foreground">{title}</h3>
        <Icon className="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold" data-testid={`stat-${title.toLowerCase().replace(/\s+/g, '-')}`}>{value}</div>
        {(trend || subtitle) && (
          <p className="text-xs text-muted-foreground mt-1">
            {trend && (
              <span className={trend.isPositive ? "text-chart-2" : "text-destructive"}>
                {trend.isPositive ? "+" : ""}{trend.value}%
              </span>
            )}
            {trend && subtitle && " "}
            {subtitle}
          </p>
        )}
      </CardContent>
    </Card>
  );
}
